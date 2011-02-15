<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	
	$result = array(
		"username" => $currentUser['fullname'],
		"userid" => $currentUser['id'],
		"loggedIn" => true
	);
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);