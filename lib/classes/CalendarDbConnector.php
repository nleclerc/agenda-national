<?php

require_once __DIR__.'/../common.php';

class CalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('calendar');
	}
	
	public function listEventsForMonth($year, $month) {
		$query =
			'select'.
			'	id,'.
			'	titre,'.
			'	annee, mois, jour '.
			'from'.
			'	iActivite '.
			'where'.
			'	annee=? and'.
			'	mois=? '.
			'order by jour asc';
		
		$foundEvents = $this->db->getList($query, $year, $month);
		$events = array();
		
		for ($i=count($foundEvents)-1; $i>=0; $i--) {
			$data = $foundEvents[$i];
			$newEvent = array(
				'id' => $data['id'],
				'title' => filterOutput($data['titre']),
				'date' => $this->formatEventDate($data)
			);
			
			array_unshift($events, $newEvent);
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