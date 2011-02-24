<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();

try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);

	$startDate = getQueryParameter('startDate');
	$endDate = getQueryParameter('endDate');
	
	if (!$startDate)
		$errorMessage = "La date de début n'a pas été spécifiée.";
	else if (!$endDate)
		$errorMessage = "La date de fin n'a pas été spécifiée.";
	else {
		$db = new CalendarDbConnector();
		$events = $db->listEventLapse($currentUser['id'], $startDate, $endDate);
		
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
			"result" => $events
		);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);