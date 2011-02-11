<?php

require_once 'common.php';

session_start();
header('Content-type: application/json; charset=utf-8');

function isLoggedIn() {
	return true;
}

function logout() {
	// conforms to national site login mechanism.
	// clears and expires the 3 cookies.
	setcookie("web_k",'',1,'/');
	setcookie("web_c",'',1,'/');
	setcookie("web_d",'',1,'/');
}

function registerUserSessionCookies($userData, $userEmail) {
	// conforms to national site login mechanism.
	function crypter($w, $h){
		// didn't try to figure out exactly what this does.
		return substr(crypt(preg_replace('/[^\d]*/', '' ,$w), chr(($h&63)+48). chr(($h>>6)%95+32)),2);
	}
	
	$web_c = null;
	$web_d = $userData['id'].' '.$userData['firstname'].' '.$userData['lastname'].'('.$userData['privilege'].')';
	
	setcookie("web_d", $web_d, 0,'/');
	setcookie("WM", $userEmail, 0,'/');
	setcookie("web_c", $web_c = crypter($web_d, floor(time()/7200)), 0,'/');
	
	return $web_c;
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
