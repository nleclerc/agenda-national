<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$loggedIn = false;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$loggedIn = true;
	$memberId = getQueryParameter('memberId');
	
	if (!$memberId)
		$errorMessage = "Identifiant de membre manquant (memberId).";
	else {
		$dbm = new LocalMemberDbConnector();
		
		$member = $dbm->findMemberPublicData($memberId);
		
		if ($member)
			$result = $member;
		else
			$errorMessage = "Membre non trouvé : $memberId";
		
		$result["username"] = $currentUser['fullname'];
		$result["userid"] = $currentUser['id'];
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["loggedIn"] = $loggedIn;
$result["errorMessage"] = $errorMessage;
echo json_encode($result);