<?php

require_once 'common.php';

session_start();
header('Content-type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function getCurrentUserData() {
	if (!isset($_SESSION['agenda_user_id']))
		throw new Exception("Vous n'êtes pas identifié.");
	
	$userdata = array();
	foreach ($_SESSION as $key => $value)
		if (isUserKey($key))
			$userdata[getUserKey($key)] = $value;
	
	$userdata['fullname'] = $userdata['firstname'].' '.$userdata['lastname'];
	return $userdata;
}

function logout() {
	foreach ($_SESSION as $key => $value)
		if (isUserKey($key)){
			unset($_SESSION[$key]);
			unset($key);
		}
}

function isUserKey($key){
	return preg_match('/^agenda_user_/', $key);
}

function getUserKey($key){
	return preg_replace('/^agenda_user_(.+)$/', '$1', $key);
}

function registerUserSession($userData) {
	foreach ($userData as $key => $value)
		$_SESSION["agenda_user_$key"] = $value;
}
