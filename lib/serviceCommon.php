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
	
	$web_c = getCookie('web_c');
	$web_d = getCookie('web_d');
	
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
	
	throw new Exception("Vous n'êtes pas identifié.");
}

function logout() {
	// conforms to national site login mechanism.
	// clears and expires the 3 cookies.
	// not really sure about what they mean...
	clearMCookie('web_k');
	clearMCookie('web_c');
	clearMCookie('web_d');
	
	// added clearing of WM since it is set on login.
	clearMCookie('WM');
}

function encrypt($value, $time){
	// didn't try to figure out exactly what this does.
	return substr(crypt(preg_replace('/[^\d]*/', '' ,$value), chr(($time&63)+48). chr(($time>>6)%95+32)),2);
}

function registerUserSessionCookies($userData) {
	// conforms to national site login mechanism.
	
	$web_c = null;
	$web_d = $userData['id'].' '.$userData['firstname'].' '.$userData['lastname'].'('.$userData['privilege'].')';
	
	setMCookie('web_d', $web_d, 0);
	setMCookie('web_c', $web_c = encrypt($web_d, floor(time()/7200)), 0);
	setMCookie('WM', $userData['email'], 0);
	
	return $web_c;
}
