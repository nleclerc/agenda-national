<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	
	$result = array(
		"username" => $currentUser['fullname'],
		"loggedIn" => true
	);
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);