<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	
	$month = getQueryParameter('month');
	$year = getQueryParameter('year');
	
	if (!$month)
		$errorMessage = "Le mois n'a pas été spécifié.";
	else if (!$year)
		$errorMessage = "L'année n'a pas été spécifié.";
	else {
		$db = new CalendarDbConnector();
		$events = $db->listEventsForMonth($year, $month);
		
		$result = array(
			"username" => $currentUser['fullname'],
			"userid" => $currentUser['id'],
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