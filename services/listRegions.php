<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();

try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);
	
	$db = new LocalMemberDbConnector();
	$regions = $db->listRegions();
	
	array_unshift($regions, array(
		'id' => 'FRA',
		'name' => 'Toutes les régions'
	));
	
	$result = array(
		"user" => $currentUser,
		"result" => $regions
	);
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);