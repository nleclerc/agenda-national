<?php

require_once 'common.php';

session_start();
header('Content-type: application/json; charset=utf-8');

function isLoggedIn() {
	return true;
}

function logout() {
	$_SESSION[CURRENT_USER] = null;
}


function getCurrentUsername() {
	return 'Joe Black';
}

function formatNb($value, $minlength) {
	$result = "$value";
	
	while (strlen($result) < $minlength)
		$result = "0$result";
	
	return $result;
}

function getQueryParameter($parmName){
	$value = null;
	
	if (isset($_POST[$parmName]))
		$value = $_POST[$parmName];
	else if (isset($_GET[$parmName]))
		$value = $_GET[$parmName];
	
	return $value;
}
