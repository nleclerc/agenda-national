<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
if (!isset($_GET['month']))
	$errorMessage = "Le mois n'a pas été spécifié.";
else if (!isset($_GET['year']))
	$errorMessage = "L'année n'a pas été spécifié.";
else {
	$month = $_GET['month'];
	$year = $_GET['year'];
	
	try {
		$db = new CalendarDbConnector();
		$events = $db->listEventsForMonth($year, $month);
		
		$currentUser = getCurrentUserData();
		
		if (!$currentUser)
			$errorMessage = "Vous n'êtes pas identifié.";
		else {
			$result = array(
				"username" => $currentUser['fullname'],
				"month" => $month,
				"year" => $year,
				"events" => $events
			);
		}
		$result["loggedIn"] = $currentUser != null;
	} catch (Exception $e) {
		$errorMessage = $e->getMessage();
	}
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);