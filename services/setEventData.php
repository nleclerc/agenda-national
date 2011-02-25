<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);
	
	$eventData = json_decode(getQueryParameter('eventData'), true);
	
	if (!$eventData)
		$errorMessage = "Données de l'évènement manquantes ou invalides (eventData).";
	else {
		$dbc = new CalendarDbConnector();
		
		if (!isset($eventData['start_date']) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $eventData['start_date']))
			throw new Exception('Date ou heure manquante ou invalide.');
		
		if (!isset($eventData['region_id']) || !$eventData['region_id'])
			throw new Exception('Région manquante.');
					
		if (!isset($eventData['title']) || !$eventData['title'])
			throw new Exception('Titre manquant.');
			
		if (!isset($eventData['description']) || !$eventData['description'])
			throw new Exception('Description manquante.');
			
		if (!isset($eventData['location']) || !$eventData['description'])
			throw new Exception('Lieu manquant.');
			
		if (!isset($eventData['max_participants']) || !$eventData['max_participants'])
			$eventData['max_participants'] = 0;
		
		$dbc->setEventData($currentUser['id'], $eventData);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);