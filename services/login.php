<?php
require '../lib/serviceCommon.php';
logout();

$errorMessage = null;
$result = array();

try {
	$dbl = new LocalMemberDbConnector();
	
	$foundUser = $dbl->findMemberAuthData(getQueryParameter('login'), getQueryParameter('password'));
	
	if (!$foundUser)
		throw new Exception('Identifiant ou mot de passe incorrect.');
	
	if (!$foundUser['subscriptionTerm'] || $foundUser['subscriptionTerm'] < date('Y-m-d'))
		throw new Exception('Votre cotisation a expirée.');
	
	registerUserSession($foundUser);
	$result["user"] = filterCurrentUserDate($foundUser);

} catch (Exception $e) {
	// Login failed so clearing auth cookies.
	logout();
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);