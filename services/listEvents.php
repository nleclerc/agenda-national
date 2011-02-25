<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();

function sortEventsOnStartDate($a, $b){
	$dateA = $a['start_date'];
	$dateB = $b['start_date'];
	
	return ($dateA < $dateB) ? -1 : 1;
}

function addIdfEvents($events, $currentUserId, $startDate, $endDate) {
	$dbidf = new IdfCalendarDbConnector();
	$idfEvents = $dbidf->listEventLapse($currentUserId, $startDate, $endDate);
	$events = array_merge($idfEvents, $events);
	uasort($events, 'sortEventsOnStartDate');
	
	// must rebuild array because of strange array behavior.
	$result = array();
	
	foreach($events as $event)
		array_push($result, $event);
	
	return $result;
}

try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);

	$startDate = getQueryParameter('startDate');
	$endDate = getQueryParameter('endDate');
	$region = strtoupper(getQueryParameter('regionId'));
	
	if (!$region)
		$region = $currentUser['region_id'];
	
	if (!$startDate)
		$errorMessage = "La date de début n'a pas été spécifiée.";
	else if (!$endDate)
		$errorMessage = "La date de fin n'a pas été spécifiée.";
	else {
		$db = new CalendarDbConnector();
		$events = $db->listEventLapse($currentUser['id'], $region, $startDate, $endDate);
		
		if ($region == 'IDF')
			$events = addIdfEvents($events, $currentUser['id'], $startDate, $endDate);
		
		$authorsIds = array();
		
		foreach($events as $event)
			array_push($authorsIds, $event['author_id']);
		
		$dbm = new LocalMemberDbConnector();
		$authors = $dbm->findMemberShortDataBatch($authorsIds, true);
		
		for ($i=0; $i<count($events); $i++) {
			$authorId = $events[$i]['author_id'];
			$authorName = "Mensa IDF"; // default name.
			
			if ($authorId > 0)
				$authorName =  $authors[$authorId]['name'];
			
			$events[$i]['author'] = $authorName;
		}
		
		$result = array(
			"user" => filterCurrentUserDate($currentUser),
			"result" => array(
				'region_id' => $region,
				'events' => $events
			)
		);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);