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
		$dbc = new IdfCalendarDbConnector();
		
		foreach ($eventData as $key => $value)
			if (is_string($value))
				$eventData[$key] = trim($value);
		
		if (!isset($eventData['start_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventData['start_date']))
			throw new Exception('Date manquante ou invalide.');
		
		if (!isset($eventData['title']) || !$eventData['title'])
			throw new Exception('Titre manquant.');
		else
			$eventData['title'] = preg_replace('/\s+/', ' ', $eventData['title']); // normalize title spaces.
			
		if (!isset($eventData['description']) || !$eventData['description'])
			throw new Exception('Description manquante.');
		
		if (!isset($eventData['max_participants']) || !$eventData['max_participants'])
			$eventData['max_participants'] = 0;
		else
			$eventData['max_participants'] = max(intval($eventData['max_participants']), 0); // filter non int and negative values.
			
		$dateTokens = explode('-', $eventData['start_date']);
		$eventData['day'] = $dateTokens[2];
		$eventData['month'] = $dateTokens[1];
		$eventData['year'] = $dateTokens[0];
		unset($eventData['start_date']);
			
		$dbc->setEventData($currentUser['id'], $eventData);
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);