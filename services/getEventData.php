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
		$dbm = new LocalMemberDbConnector();
		
		$eventDetails = $dbc->findEventData($eventId);
		$userId = $currentUser['id'];
		
		if ($eventDetails) {
			$authorId = $eventDetails['authorId'];
			
			// default author details.
			$authorDetails = array(
				'name' => 'Mensa IDF',
				'email' => 'idf@mensa.fr'
			);
			
			// if not mensa IDF
			if ($authorId > 0)
				$authorDetails = $dbm->findMemberShortData($authorId);
			
			if (!$authorDetails)
				throw new Exception('Autheur inconnu : '.$authorId);
			
			$eventDetails['author'] = $authorDetails['name'];
			
			if (isset($authorDetails['email']))
				$eventDetails['authorEmail'] = $authorDetails['email'];
			
			$eventDetails["isParticipating"] = in_array($userId, $eventDetails['participants']);
			$eventDetails['participants'] = $dbm->findMemberShortDataBatch($eventDetails['participants']);
			
			$result = $eventDetails;
		}
		else
			$errorMessage = "Evènement inconnu : $eventId";
		
		$result["username"] = $currentUser['fullname'];
		$result["userid"] = $userId;
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);