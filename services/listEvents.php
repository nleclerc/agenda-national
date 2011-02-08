<?php
require '../lib/serviceCommon.php';

$errorMessage = '';
$result = array();
	
if (!isset($_GET['month']))
	$errorMessage = "Le mois n'a pas été spécifié.";
else if (!isset($_GET['year']))
	$errorMessage = "L'année n'a pas été spécifié.";
else {
	$month = $_GET['month'];
	$year = $_GET['year'];
	
	try {
		$db = openDb('agenda');
		
		$query =
			'select'.
			'	id,'.
			'	titre,'.
			'	annee, mois, jour '.
			'from'.
			'	iActivite '.
			'where'.
			'	annee=? and'.
			'	mois=? '.
			'order by jour asc';
		
		$foundEvents = $db->getList($query, $year, $month);
		$events = array();
		
		for ($i=count($foundEvents)-1; $i>=0; $i--) {
			$data = $foundEvents[$i];
			$newEvent = array(
				'id' => $data['id'],
				'title' => filterOutput($data['titre']), // TODO : remove html decode
				'date' => formatEventDate($data)
			);
			
			array_unshift($events, $newEvent);
		}
		
		
		$isLoggedIn = isLoggedIn();
		
		if (!$isLoggedIn)
			$errorMessage = "Vous n'êtes pas identifié.";
		else {
			$result = array(
				"username" => getCurrentUsername(),
				"month" => $month,
				"year" => $year,
				"events" => $events
			);
		}
		$result["loggedIn"] = $isLoggedIn;
	} catch (Exception $e) {
		$errorMessage = $e->getMessage();
	}
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);
?>