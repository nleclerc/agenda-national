<?php
require '../lib/serviceCommon.php';
logout();

$errorMessage = null;
$loggedIn = false;
$result = array();

try {
	$dbl = new LocalMemberDbConnector();
	$dbr = new RemoteMemberDbConnector();
	
	$foundUser = $dbl->findMemberAuthData($dbr, getQueryParameter('login'), getQueryParameter('password'));

	// No need for check because either user is found or exception is raised.
	registerUserSessionCookies($foundUser);
	
	$result['username'] = $foundUser['firstname'].' '.$foundUser['lastname'];
	$loggedIn = true;
	
} catch (Exception $e) {
	// Login failed so clearing auth cookies.
	logout();
	
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
$result['loggedIn'] = $loggedIn;
echo json_encode($result);