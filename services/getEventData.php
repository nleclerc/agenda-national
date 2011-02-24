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
		$dbm = new LocalMemberDbConnector();
		
		$eventDetails = $dbc->findEventData($eventId);
		
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
			
			$eventDetails["isParticipating"] = in_array($currentUser['id'], $eventDetails['participants']);
			$eventDetails['participants'] = $dbm->findMemberShortDataBatch($eventDetails['participants']);
			
			$result['result'] = $eventDetails;
		}
		else
			$errorMessage = "Evènement inconnu : $eventId";
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);