<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();

try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);
	
	$db = new LocalMemberDbConnector();
	$regions = $db->listRegions();
	
	$national = array(
		'id' => 'FRA',
		'name' => 'National'
	);
	
	array_unshift($regions, $national);
	
	$result = array(
		"user" => $currentUser,
		"result" => $regions
	);
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);