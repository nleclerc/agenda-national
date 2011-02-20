<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();

try {
	$currentUser = getCurrentUserData();
	$currentId = $currentUser['id'];
	$loggedIn = true;
	
	$startDate = getQueryParameter('startDate');
	$endDate = getQueryParameter('endDate');
	
	if (!$startDate)
		$errorMessage = "La date de début n'a pas été spécifiée.";
	else if (!$endDate)
		$errorMessage = "La date de fin n'a pas été spécifiée.";
	else {
		$db = new CalendarDbConnector();
		$events = $db->listEventLapse($currentId, $startDate, $endDate);
		
		$authorsIds = array();
		
		foreach($events as $event)
			array_push($authorsIds, $event['author']);
		
		$dbm = new LocalMemberDbConnector();
		$authors = $dbm->findMemberShortDataBatch($authorsIds, true);
		
		for ($i=0; $i<count($events); $i++) {
			$authorId = $events[$i]['author'];
			$authorName = "Mensa IDF"; // default name.
			
			if ($authorId > 0)
				$authorName =  $authors[$authorId]['name'];
			
			$events[$i]['author'] = $authorName;
		}
		
		$result = array(
			"username" => $currentUser['fullname'],
			"userid" => $currentId,
			"events" => $events
		);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);