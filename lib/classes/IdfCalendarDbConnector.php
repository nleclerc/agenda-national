<?php

class IdfCalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('idfCalendar');
	}
	
	public function deleteEvent($currentMemberId, $eventId) {
		$this->checkOwner($currentMemberId, $eventId);
		
		$this->db->execute('DELETE FROM iActivite WHERE id=?',$eventId); // delete event.
		$this->db->execute('DELETE FROM iInscription WHERE id=?',$eventId); // delete participations.
	}
	
	private function checkOwner($currentMemberId, $eventId) {
		$ownerId = $this->getOwnerId($eventId);
		
		if (!$ownerId)
			throw new Exception("Evènement non trouvé: $eventId");
		elseif ($ownerId != $currentMemberId)
			throw new Exception("Vous n'êtes pas propriétaire de cet évènement.");
	}
	
	private function getOwnerId($eventId) {
		$row = $this->db->getRow('SELECT membre as author FROM iActivite WHERE id=?', $eventId);
		
		if ($row)
			return intval($row['author']);
		
		return null;
	}
	
	private function listEvents($currentMemberId, $whereClause, $parms=null) {
		$query =
			'select'.
			'	a.id,'.
			'	titre as title,'.
			'	annee, mois, jour,'.
			'	if(i.ref is not null,true,false) as is_participating,'.
			'	membre as author_id,'.
			'	limite as max_participants,'.
			'	count(ii.ref) as participant_count '.
			'from'.
			'	iActivite a '.
			'left join'.
			'	iInscription i on a.id = i.id and i.ref = ? '.
			'left join'.
			'	iInscription ii on a.id = ii.id ';
		
		if ($whereClause)
			$query.= 'where '.$whereClause;
		
		$query.=' group by a.id '.
			'order by annee asc, mois asc, jour asc';
		
		$actualParms = null;
		
		if (is_array($parms))
			$actualParms = $parms;
		else
			$actualParms = array_slice(func_get_args(), 2);
		
		array_unshift($actualParms, $currentMemberId);
		
		$foundEvents = $this->db->getList($query, $actualParms);
		$events = array();
		
		for ($i=count($foundEvents)-1; $i>=0; $i--) {
			$data = $foundEvents[$i];
			$data['id'] = intval($data['id']);
			$data['start_date'] = $this->formatEventDate($data);
			$data['is_participating'] = $data['is_participating'] == true; // converts because query returns 0 or 1 as a string.
			$data['is_idf_event'] = true;
			$data['region_id'] = 'IDF';
			$data['max_participants'] = intval($data['max_participants']);
			
			unset($data['annee']);
			unset($data['mois']);
			unset($data['jour']);
			
			array_unshift($events, $data);
		}
		
		return $events;
	}
	
	public function listEventLapse($currentMemberId, $startDate, $endDate) {
		$start = $this->parseDate($startDate);
		$end = $this->parseDate($endDate);
		
		// where clause mess because of date storage format.
		
		$where = '';
		$where.= '(annee = ? and mois = ? and jour >= ?) OR '; // after start date
		$where.= '(annee = ? and mois = ? and jour <= ?) OR '; // before end date
		
		$sameYear = $start[0] == $end[0];
		
		$parms = array();
		array_push($parms, $start[0]);
		array_push($parms, $start[1]);
		array_push($parms, $start[2]);
		array_push($parms, $end[0]);
		array_push($parms, $end[1]);
		array_push($parms, $end[2]);
		
		if ($sameYear) {
			$where.= '(annee = ? and mois > ? and mois < ?)';
			array_push($parms, $start[0]);
			array_push($parms, $start[1]);
			array_push($parms, $end[1]);
		} else {
			$where.= '(annee = ? and mois > ?) OR (annee = ? and mois < ?)';
			array_push($parms, $start[0]);
			array_push($parms, $start[1]);
			array_push($parms, $end[0]);
			array_push($parms, $end[1]);
		}
		
		return $this->listEvents($currentMemberId, $where, $parms);
	}
	
	private function parseDate($datestr){
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $datestr))
			throw new Exception('Date invalide: '.$datestr);
		
		return explode('-', $datestr);
	}
		
	public function setEventData($currentMemberId, $eventData) {
		// filter text to avoid script injection.
		$eventData['title'] = $this->filterHtmlString($eventData['title']);
		$eventData['description'] = $this->filterHtmlString($eventData['description']);
		
		if (isset($eventData['id']) && $eventData['id'])
			$this->updateEvent($currentMemberId, $eventData);
		else
			throw new Exception("La création de nouveaux évènement dans l'ancien agenda est interdite.");
	}
	
	private function updateEvent($authorId, $eventData) {
		$this->checkOwner($authorId, $eventData['id']);
		
		$query = 'UPDATE iActivite '.
				'SET titre=:title, jour=:day, mois=:month, annee=:year, membre=:author_id, texte=:description, limite=:max_participants '.
				'WHERE id=:id';
		
		$parms = array(':author_id'=>$authorId);
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function filterHtmlString($str) {
		$result = $str;
		$result = preg_replace('/&/', '&amp;', $result);
		$result = preg_replace('/</', '&lt;', $result);
		return $result;
	}
	
	public function findEventData($eventId) {
		$query =
			'SELECT'.
			'	id,'.
			'	titre as title,'.
			'	jour,'.
			'	mois,'.
			'	annee,'.
			'	membre as author_id,'.
			'	texte as description,'.
			'	limite as max_participants '.
			'FROM '.
			'	iActivite '.
			'WHERE '.
			'	id=?';
		
		$foundEvent = $this->db->getRow($query, $eventId);
		
		if ($foundEvent) {
			$result = array(
				'id' => intval($foundEvent['id']),
				'title' => $foundEvent['title'],
				'start_date' => $this->formatEventDate($foundEvent),
				'description' => $foundEvent['description'],
				'max_participants' => intval($foundEvent['max_participants']),
				'author_id' => intval($foundEvent['author_id']),
				'participants' => $this->findParticipants($eventId),
				'region_id' => 'IDF'
			);
			
			return $result;
		}
		
		return null;
	}
	
	public function addParticipant($eventId, $memberId) {
		$this->removeParticipant($eventId, $memberId);
		return $this->db->execute("INSERT INTO iInscription (id, ref) VALUES (?, ?)", $eventId, $memberId);
	}
	
	public function removeParticipant($eventId, $memberId) {
		return $this->db->execute("DELETE FROM iInscription WHERE id=? AND ref=?", $eventId, $memberId);
	}
	
	private function findParticipants($eventId) {
		$foundIds = $this->db->getList('SELECT ref as memberId FROM iInscription WHERE id=?', $eventId);
		$result = array();
		
		foreach($foundIds as $insc)
			array_push($result, $insc['memberId']);
		
		return $result;
	}
	
	private function formatEventDate($event) {
		return $event['annee'].'-'.formatNb($event['mois'],2).'-'.formatNb($event['jour'],2);
	}
}