<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);
	
	$eventId = getQueryParameter('eventId');
	
	if (!$eventId)
		$errorMessage = "Identifiant d'événement manquant (eventId).";
	else {
		$dbc = new CalendarDbConnector();
		$dbc->deleteEvent($currentUser['id'], $eventId);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);