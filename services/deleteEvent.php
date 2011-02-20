<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	$eventId = getQueryParameter('eventId');
	
	if (!$eventId)
		$errorMessage = "Identifiant d'événement manquant (eventId).";
	else {
		$dbc = new CalendarDbConnector();
		
		$userId = $currentUser['id'];
		
		$dbc->deleteEvent($userId, $eventId);
		
		$result["username"] = $currentUser['fullname'];
		$result["userid"] = $userId;
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);