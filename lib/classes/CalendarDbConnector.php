<?php

class CalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('calendar');
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
			'	if(i.ref is not null,true,false) as isParticipating,'.
			'	membre as author,'.
			'	limite as maxParticipants,'.
			'	count(ii.ref) as participantCount '.
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
			$data['date'] = $this->formatEventDate($data);
			$data['isParticipating'] = $data['isParticipating'] == true; // converts because query returns 0 or 1 as a string.
			
			unset($data['annee']);
			unset($data['mois']);
			unset($data['jour']);
			
			array_unshift($events, $data);
		}
		
		return $events;
	}
		
	public function listEventsForMonth($currentMemberId, $year, $month) {
		return $this->listEvents($currentMemberId, 'annee=? and mois=?', $year, $month);
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
		if (!preg_match('/\d{2}-\d{2}-\d{4}/', $datestr))
			throw new Exception('Date invalide: '.$datestr);
		
		return array_reverse(explode('-', $datestr));
	}
		
	public function setEventData($currentMemberId, $eventData) {
		// filter text to avoid script injection.
		$eventData['title'] = $this->filterHtmlString($eventData['title']);
		$eventData['description'] = $this->filterHtmlString($eventData['description']);
		
		if (isset($eventData['id']) && $eventData['id'])
			$this->updateEvent($currentMemberId, $eventData);
		else
			$this->createEvent($currentMemberId, $eventData);
	}
	
	private function createEvent($authorId, $eventData) {
		$query = 'INSERT INTO iActivite (titre, jour, mois, annee, membre, texte, limite) '.
				'VALUES (:title,:day,:month,:year,:authorId,:description,:maxParticipants)';
		
		$parms = array(':authorId'=>$authorId);
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function updateEvent($authorId, $eventData) {
		$this->checkOwner($authorId, $eventData['id']);
		
		$query = 'UPDATE iActivite '.
				'SET titre=:title, jour=:day, mois=:month, annee=:year, membre=:authorId, texte=:description, limite=:maxParticipants '.
				'WHERE id=:id';
		
		$parms = array(':authorId'=>$authorId);
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function filterHtmlString($str) {
		$result = $str;
		$result = preg_replace('/&/', '&amp;', $result);
		$result = preg_replace('/</', '&lt;', $result);
		$result = preg_replace('/>/', '&gt;', $result);
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
			'	membre as authorId,'.
			'	texte as description,'.
			'	limite as maxParticipants '.
			'FROM '.
			'	iActivite '.
			'WHERE '.
			'	id=?';
		
		$foundEvent = $this->db->getRow($query, $eventId);
		
		if ($foundEvent) {
			$result = array(
				'id' => $foundEvent['id'],
				'title' => $foundEvent['title'],
				'date' => formatNb($foundEvent['jour'], 2).'/'.formatNb($foundEvent['mois'], 2).'/'.formatNb($foundEvent['annee'], 4),
				'description' => $foundEvent['description'],
				'maxParticipants' => $foundEvent['maxParticipants'],
				'authorId' => intval($foundEvent['authorId']),
				'participants' => $this->findParticipants($eventId)
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
		return formatNb($event['jour'],2).'/'.formatNb($event['mois'],2).'/'.$event['annee'];
	}
}