<?php

function __autoload($class_name) {
    require "classes/$class_name.php";
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


function clearMCookie($name) {
	setMCookie($name,'',1);
}

function setMCookie ($name, $value = null, $expire = null, $path = '/', $domain = null) {
	// If on actual mensa.fr server then set domain as main site code requires.
	if (preg_match('/mensa.fr$/', $_SERVER['SERVER_NAME']))
		$domain = '.mensa.fr';
	
	setcookie($name, $value, $expire, $path, $domain);
}

function getCookie($name) {
	if (isset($_COOKIE[$name]))
		return $_COOKIE[$name];
	
	return null;
}