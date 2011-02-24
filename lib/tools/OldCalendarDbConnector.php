<?php

class OldCalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('oldCalendar');
	}
	
	public function listEvents($whereClause=null, $parms=null) {
		$query =
			'select'.
			'	id,'.
			'	titre as title,'.
			'	jour,'.
			'	mois,'.
			'	annee,'.
			'	membre as author_id,'.
			'	texte as description,'.
			'	limite as max_participants '.
			'from'.
			'	iActivite  ';
		
		if ($whereClause)
			$query.= 'where '.$whereClause;
		
		$query.= 'order by annee asc, mois asc, jour asc';
		
		$actualParms = null;
		
		if (is_array($parms))
			$actualParms = $parms;
		else
			$actualParms = array_slice(func_get_args(), 1);
		
		$foundEvents = $this->db->getList($query, $actualParms);
		$events = array();
		
		for ($i=count($foundEvents)-1; $i>=0; $i--) {
			$data = $foundEvents[$i];
			$data['start_date'] = $this->formatEventDate($data);
			
			unset($data['annee']);
			unset($data['mois']);
			unset($data['jour']);
			
			$data['title'] = $this->filterHtmlString($data['title']);
			$data['description'] = $this->filterHtmlString($data['description']);
			
			array_unshift($events, $data);
		}
		
		return $events;
	}
	
	private function parseDate($datestr){
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $datestr))
			throw new Exception('Date invalide: '.$datestr);
		
		return explode('-', $datestr);
	}
	
	private function filterHtmlString($str) {
		$result = $str;
		$result = html_entity_decode($result, ENT_QUOTES, 'utf-8');
		$result = strip_tags($result);
		return $result;
	}
	
	public function findParticipations() {
		return $this->db->getList('SELECT id as event_id, ref as member_id FROM iInscription', $eventId);
	}
	
	private function formatEventDate($event) {
		return $event['annee'].'-'.formatNb($event['mois'],2).'-'.formatNb($event['jour'],2);
	}
}