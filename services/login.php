<?php
require '../lib/serviceCommon.php';
logout();

$errorMessage = null;
$result = array();

try {
	$dbl = new LocalMemberDbConnector();
	$dbr = new RemoteMemberDbConnector();
	
	$foundUser = $dbl->findMemberAuthData($dbr, getQueryParameter('login'), getQueryParameter('password'));

	// No need for check because either user is found or exception is raised.
	
	$result = $foundUser;
	
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);