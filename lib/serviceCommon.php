<?php

require_once 'common.php';

session_start();
header('Content-type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function getCurrentUserData() {
	// adapted from IDF code.
	// not exactly sure how that works or what it's supposed to do...
	
	$memberId = null;
	$firstname = null;
	$lastname = null;
	$privilege = null;
	
	$web_c = $_COOKIE['web_c'];
	$web_d = $_COOKIE['web_d'];
	
	if ($web_c) {
		$h = floor(time()/7200);
		if ($web_c != ($wc = encrypt($web_d, $h))) {
			if ($web_c != encrypt($web_d, $h-1)) { 
				logout();
			}else{
				setcookie("web_c", $web_c = $wc, 0, '/');
				$memberId = $firstname = $lastname = $privilege = NULL;
			}
		}
		if ($web_d){ 
			list($memberId, $firstname, $lastname) = explode(" ",$web_d,3); 
			list($lastname, $privilege) = explode("(",$lastname);
		}
	}
	
	if ($memberId)
		return array(
			'id' => $memberId,
			'lastname' => $lastname,
			'firstname' => $firstname,
			'fullname' => "$firstname $lastname",
			'privilege' => $privilege
		);
	
	return null;
}

function logout() {
	// conforms to national site login mechanism.
	// clears and expires the 3 cookies.
	// not really sure about what they mean...
	setcookie("web_k",'',1,'/');
	setcookie("web_c",'',1,'/');
	setcookie("web_d",'',1,'/');
	
	// added clearing of WM since it is set on login.
	setcookie("WM",'',1,'/');
}

function encrypt($value, $time){
	// didn't try to figure out exactly what this does.
	return substr(crypt(preg_replace('/[^\d]*/', '' ,$value), chr(($time&63)+48). chr(($time>>6)%95+32)),2);
}

/*
function registerUserSessionCookies($userData) {
	// conforms to national site login mechanism.
	
	$web_c = null;
	$web_d = $userData['id'].' '.$userData['firstname'].' '.$userData['lastname'].'('.$userData['privilege'].')';
	
	$userEmail = '';
	
	if (isset($userData['contacts'])) {
		$contacts = $userData['contacts'];
		for ($i=0; $i<)
	}
	
	setcookie("web_d", $web_d, 0,'/');
	setcookie("WM", $userEmail, 0,'/');
	setcookie("web_c", $web_c = encrypt($web_d, floor(time()/7200)), 0,'/');
	
	return $web_c;
}
*/

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
