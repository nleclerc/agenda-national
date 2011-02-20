<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	$eventData = json_decode(getQueryParameter('eventData'), true);
	
	if (!$eventData)
		$errorMessage = "Données de l'évènement manquantes ou invalides (eventData).";
	else {
		$dbc = new CalendarDbConnector();
		
		$userId = $currentUser['id'];
		
		if (!isset($eventData['date']) || !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $eventData['date']))
			throw new Exception('Date manquante ou invalide.');
		
		$dateTokens = explode('/', $eventData['date']);
		$eventData['day'] = $dateTokens[0];
		$eventData['month'] = $dateTokens[1];
		$eventData['year'] = $dateTokens[2];
		unset($eventData['date']);

		if (!isset($eventData['title']) || !$eventData['title'])
			throw new Exception('Titre manquant.');

		if (!isset($eventData['description']) || !$eventData['description'])
			throw new Exception('Description manquante.');

		if (!isset($eventData['maxParticipants']) || !$eventData['maxParticipants'])
			$eventData['maxParticipants'] = 0;
		
		$dbc->setEventData($userId, $eventData);
		
		$result["username"] = $currentUser['fullname'];
		$result["userid"] = $userId;
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);