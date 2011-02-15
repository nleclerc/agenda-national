<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();

try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	
	$userId = $currentUser['id'];
	$eventId = getQueryParameter('eventId');
	
	if (!$userId)
		$errorMessage = "Identifiant d'événement manquant (eventId).";
	else {
		$dbc = new CalendarDbConnector();
		$dbc->addParticipant($eventId, $userId);
	}
	
	$result["username"] = $currentUser['fullname'];
	$result['userid'] = $userId;
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);