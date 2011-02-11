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

	private function formatEventDate($event) {
		return formatNb($event['jour'],2).'/'.formatNb($event['mois'],2).'/'.$event['annee'];
	}
}