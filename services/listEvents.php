<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$currentId = $currentUser['id'];
	$loggedIn = true;
	
	$month = getQueryParameter('month');
	$year = getQueryParameter('year');
	
	if (!$month)
		$errorMessage = "Le mois n'a pas été spécifié.";
	else if (!$year)
		$errorMessage = "L'année n'a pas été spécifié.";
	else {
		$db = new CalendarDbConnector();
		$events = $db->listEventsForMonth($currentId, $year, $month);
		
		$authorsIds = array();
		
		foreach($events as $event)
			array_push($authorsIds, $event['author']);
		
		$dbm = new LocalMemberDbConnector();
		$authors = $dbm->findMemberShortDataBatch($authorsIds);
		
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
			"month" => $month,
			"year" => $year,
			"events" => $events
		);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);