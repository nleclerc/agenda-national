<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	
	if (!isset($_GET['month']))
		$errorMessage = "Le mois n'a pas été spécifié.";
	else if (!isset($_GET['year']))
		$errorMessage = "L'année n'a pas été spécifié.";
	else {
		$month = $_GET['month'];
		$year = $_GET['year'];
	
		$db = new CalendarDbConnector();
		$events = $db->listEventsForMonth($year, $month);
		
		$result = array(
			"username" => $currentUser['fullname'],
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