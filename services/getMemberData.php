<?php
require '../lib/serviceCommon.php';

$errorMessage = null;
$result = array();
	
try {
	$currentUser = getCurrentUserData();
	$result["user"] = filterCurrentUserDate($currentUser);
	
	$memberId = getQueryParameter('memberId');
	
	if (!$memberId)
		$errorMessage = "Identifiant de membre manquant (memberId).";
	else {
		$dbm = new LocalMemberDbConnector();
		
		$member = $dbm->findMemberPublicData($memberId);
		
		if ($member)
			$result['result'] = $member;
		else
			$errorMessage = "Membre non trouvé : $memberId";
	}
} catch (Exception $e) {
	$errorMessage = $e->getMessage();
}

$result["errorMessage"] = $errorMessage;
echo json_encode($result);