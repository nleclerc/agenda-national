<?php
require '../lib/serviceCommon.php';
logout();

$errorMessage = null;
$loggedIn = false;
$result = array();

try {
	$dbl = new LocalMemberDbConnector();
	
	$foundUser = $dbl->findMemberAuthData(getQueryParameter('login'), getQueryParameter('password'));
	
	if (!$foundUser)
		throw new Exception('Identifiant ou mot de passe incorrect.');
	
	if (!$foundUser['subscriptionTerm'] || $foundUser['subscriptionTerm'] < date('Y-m-d'))
		throw new Exception('Votre cotisation a expirée.');
	
	registerUserSession($foundUser);
	
	$result['username'] = $foundUser['firstname'].' '.$foundUser['lastname'];
	$result['userid'] = $foundUser['id'];
	$loggedIn = true;
} catch (Exception $e) {
	// Login failed so clearing auth cookies.
	logout();
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
$result['loggedIn'] = $loggedIn;
echo json_encode($result);