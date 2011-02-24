<?php

class CalendarDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('calendar');
	}
	
	public function deleteEvent($currentMemberId, $eventId) {
		$this->checkOwner($currentMemberId, $eventId);
		
		$this->db->execute('DELETE FROM event WHERE id=?',$eventId); // delete event.
		$this->db->execute('DELETE FROM event_participation WHERE event_id=?',$eventId); // delete participations.
	}
	
	private function checkOwner($currentMemberId, $eventId) {
		$ownerId = $this->getOwnerId($eventId);
		
		if (!$ownerId)
			throw new Exception("Evènement non trouvé: $eventId");
		elseif ($ownerId != $currentMemberId)
			throw new Exception("Vous n'êtes pas propriétaire de cet évènement.");
	}
	
	private function getOwnerId($eventId) {
		$row = $this->db->getRow('SELECT author_id FROM event WHERE id=?', $eventId);
		
		if ($row)
			return intval($row['author_id']);
		
		return null;
	}
	
	private function listEvents($currentMemberId, $whereClause, $parms=null) {
		$query =
			'select'.
			'	e.id,'.
			'	modification_date,'.
			'	title,'.
			'	author_id,'.
			'	region_id,'.
			'	start_date,'.
			'	if(p.member_id is not null,true,false) as is_participating,'.
			'	max_participants,'.
			'	count(pcount.member_id) as participant_count '.
			'from'.
			'	event e '.
			'left join'.
			'	event_participation p on p.event_id = e.id and p.member_id = ? '.
			'left join'.
			'	event_participation pcount on pcount.event_id = e.id ';
		
		if ($whereClause)
			$query.= 'where '.$whereClause;
		
		$query.=' group by e.id '.
			'order by start_date asc';
		
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
			$data['is_participating'] = $data['is_participating'] == true; // converts because query returns 0 or 1 as a string.
			
			array_unshift($events, $data);
		}
		
		return $events;
	}
		
	public function listEventsForMonth($currentMemberId, $year, $month) {
		return $this->listEvents($currentMemberId, 'year(start_date)=? and month(start_date)=?', $year, $month);
	}
		
	public function listEventLapse($currentMemberId, $startDate, $endDate) {
		$this->checkDate($startDate);
		$this->checkDate($endDate);
		
		// where clause mess because of date storage format.
		
		$where = '';
		$where.= 'date(start_date) >= ? AND '; // equal or after start date
		$where.= 'date(start_date) <= ? '; // equal or before end date
		
		return $this->listEvents($currentMemberId, $where, $startDate, $endDate);
	}
	
	private function checkDate($datestr){
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $datestr))
			throw new Exception('Date invalide: '.$datestr);
	}
		
	public function setEventData($currentMemberId, $eventData) {
		if (isset($eventData['id']) && $eventData['id'])
			$this->updateEvent($currentMemberId, $eventData);
		else
			$this->createEvent($currentMemberId, $eventData);
	}
	
	public function importEvent($eventData) {
		// filter text to avoid script injection.
		$eventData = $this->filterEventData($eventData);
		
		$query = 'INSERT INTO event (id, author_id, region_id, start_date, title, location, description, max_participants) '.
				'VALUES (:id, :author_id, :region_id, :start_date, :title, :location, :description, :max_participants)';
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function createEvent($authorId, $eventData) {
		// filter text to avoid script injection.
		$eventData = $this->filterEventData($eventData);
		
		$query = 'INSERT INTO event (author_id, region_id, start_date, title, location, description, max_participants) '.
				'VALUES (:author_id, :region_id, :start_date, :title, :location, :description, :max_participants)';
		
		$parms = array(':author_id'=>$authorId);
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function updateEvent($authorId, $eventData) {
		// filter text to avoid script injection.
		$eventData = $this->filterEventData($eventData);
		
		$this->checkOwner($authorId, $eventData['id']);
		
		$query = 'UPDATE event '.
				'SET author_id=:author_id, region_id=:region_id, start_date=:start_date, '.
				'title=:title, location=:location, description=:description, max_participants=:max_participants '.
				'WHERE id=:id';
		
		$parms = array(':author_id'=>$authorId);
		
		foreach($eventData as $key => $value)
			$parms[":$key"] = $value;
		
		$this->db->execute($query, $parms);
	}
	
	private function filterEventData($eventData) {
		$eventData['title'] = $this->filterHtmlString($eventData['title']);
		$eventData['description'] = $this->filterHtmlString($eventData['description']);
		$eventData['location'] = $this->filterHtmlString($eventData['location']);
		return $eventData;
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
			'select'.
			'	id,'.
			'	modification_date,'.
			'	title,'.
			'	author_id,'.
			'	region_id,'.
			'	start_date,'.
			'	if(p.member_id is not null,true,false) as is_participating,'.
			'	max_participants'.
			'from'.
			'	event '.
			'WHERE '.
			'	id=?';
		
		$foundEvent = $this->db->getRow($query, $eventId);
		
		if ($foundEvent) {
			$foundEvent['participants'] = $this->findParticipants($eventId);
			return $result;
		}
		
		return null;
	}
	
	public function addParticipant($eventId, $memberId) {
		$this->removeParticipant($eventId, $memberId);
		return $this->db->execute("INSERT INTO event_participation (event_id, member_id) VALUES (?, ?)", $eventId, $memberId);
	}
	
	public function removeParticipant($eventId, $memberId) {
		return $this->db->execute("DELETE FROM event_participation WHERE event_id=? AND member_id=?", $eventId, $memberId);
	}
	
	private function findParticipants($eventId) {
		$foundIds = $this->db->getList('SELECT member_id FROM event_participation WHERE event_id=?', $eventId);
		$result = array();
		
		foreach($foundIds as $participation)
			array_push($result, $participation['member_id']);
		
		return $result;
	}
}