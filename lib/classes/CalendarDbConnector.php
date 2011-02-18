<?php

class CalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('calendar');
	}
	
	public function listEventsForMonth($currentMemberId, $year, $month) {
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
			'	iInscription ii on a.id = ii.id '.
			'where'.
			'	annee=? and'.
			'	mois=? '.
			'group by a.id '.
			'order by jour asc';
		
		$foundEvents = $this->db->getList($query, $currentMemberId, $year, $month);
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
				'authorId' => $foundEvent['authorId'],
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