<?php

session_start();

function __autoload($class_name) {
    require "classes/$class_name.php";
}

function openDb($dbname) {
	return new EzPDO($dbname);
}

function open4D() {
	require(__DIR__.'/../conf/database.php');
	
	$connectionInfo = $databases['4D'];
	
	try {
		$client = new soapClient('http://'.$connectionInfo['host'].':80/4DWSDL',array('trace'=>1,'encoding'=>$connectionInfo['charset']));
		$client->__setLocation('http://'.$connectionInfo['host'].':80/4DSOAP');
		return $client;
	} catch(Exception $e) {
		throw new Exception('Echec de la connexion 4D : '.$e->getMessage());
	}
}

function getQueryParameter($parmName){
	$value = null;
	
	if (isset($_POST[$parmName]))
		$value = $_POST[$parmName];
	else if (isset($_GET[$parmName]))
		$value = $_GET[$parmName];
	
	return $value;
}

function hasRunningSubscription($ezpdo, $idMembre) {
	$idMembre = intval($idMembre);
	$currentDate = date('Y-m-d');
	$foundSubscription = $ezpdo->getRow(
		'SELECT * FROM Cotisation WHERE idMembre = ? AND debut <= ? AND fin >= ?  ORDER BY fin DESC',
		$idMembre, $currentDate, $currentDate
	);
	
	return $foundSubscription != false;
}

function findOrFetchMember($ezpdo, $login, $password){
	$foundMember = findMember($ezpdo, $login, $password);
	
	if (!$foundMember){
		$fetchedData = fetchMemberFrom4D($login, $password);
		$ezpdo->execute(
			'UPDATE Membre SET idWeb = ?, passWeb = ?, password = ? WHERE idMembre = ?',
			$login, $password, $password, $fetchedData['id']
		);
		
		$foundMember = findMember($ezpdo, $login, $password);
	}
	
	return $foundMember;
}

function findMember($ezpdo, $login, $password) {
	$foundMember = $ezpdo->getRow(
		'SELECT * FROM Membre WHERE ((idWeb = ? AND passWeb = ?) OR (idMembre = ? AND password = ?))',
		$login, $password,
		intval($login), $password
	);
	
	return $foundMember;
}

function parseMemberName($fullname) {
	$firstnameTokens = array();
	
	$tokens = explode(' ', $fullname);
	
	while (count($tokens) && $tokens[0] != mb_strtoupper($tokens[0])) {
		array_push($firstnameTokens, array_shift($tokens));
	}
	
	return array(
		'firstname' => implode(' ', $firstnameTokens),
		'lastname' => implode(' ', $tokens)
	);
}

function fetchMemberFrom4D($login, $password) {
	$client = open4D();
	
	$rawResult = $client->ws_xml_acces($login, $password);
	
	if(is_soap_fault($rawResult))
		throw new Exception('Echec de la connexion au serveur 4D : '.$rawResult->faultcode.' :: '.$rawResult->faultstring);
	
	$doc = new SimpleXMLElement(utf8_encode($rawResult));
	
	// the message test might be pointless
	if ($doc->Erreur != 1 || $doc->Message == 'N/A')
		throw new Exception('Erreur de Login ou/et Mot de passe ou cotisation arrivée à échéance.');
	
	$foundMember = $doc->membre;
	
	if ($foundMember->ref < 1)
		throw new Exception('Problème de numéro de membre, veuillez contactez l\'admistrateur.');
	
	$memberData = parseMemberName($foundMember->nom);
	
	$memberData['id'] = (int)$foundMember->ref;
	$memberData['region'] = (string)$foundMember->region;
	$memberData['email'] = (string)$foundMember->courriel;
	$memberData['subscriptionTerm'] = preg_replace('%(\d{2})/(\d{2})/(\d{2})%', '$1-$2-20$3', $foundMember->echeance);
	$memberData['privilege'] = (int)$foundMember->droits;
	
	return $memberData;
}

function logout() {
	$_SESSION[CURRENT_USER] = null;
}
