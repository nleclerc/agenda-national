<?php
require __DIR__.'/../../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);

	$eventId = getQueryParameter('eventId');
	
	if (!$eventId)
		$errorMessage = "Identifiant d'événement manquant (eventId).";
	else {
		$dbc = new IdfCalendarDbConnector();
		$dbm = new LocalMemberDbConnector();
		
		$eventDetails = $dbc->findEventData($eventId);
		
		if ($eventDetails) {
			$authorId = $eventDetails['author_id'];
			
			// default author details.
			$authorDetails = array(
				'id' => 0,
				'name' => 'Mensa IDF',
				'email' => 'idf@mensa.fr'
			);
			
			// if not mensa IDF
			if ($authorId > 0)
				$authorDetails = $dbm->findMemberShortData($authorId);
			
			if (!$authorDetails)
				throw new Exception('Autheur inconnu : '.$authorId);
			
			$eventDetails['author'] = $authorDetails;
			$eventDetails["is_participating"] = in_array($currentUser['id'], $eventDetails['participants']);
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