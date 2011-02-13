<?php
require '../lib/serviceCommon.php';
logout();

$errorMessage = null;
$result = array();

try {
	$dbl = new LocalMemberDbConnector();
	$dbr = new RemoteMemberDbConnector();
	
	$foundUser = $dbl->findOrFetchMember($dbr, getQueryParameter('login'), getQueryParameter('password'));
	
	if ($foundUser)
		;
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);