<?php

function __autoload($class_name) {
    require "classes/$class_name.php";
}

function filterOutput($value) {
	$result = iconv("iso-8859-15", "utf8", $value);
	
	// workarounds for charset pb.
	$result = preg_replace('/\x{0080}/u', '€', $result); // Fixes euro char.
	$result = preg_replace('/\x{0092}/u', "'", $result); // Fixes some apostrophes.
	$result = preg_replace('/\x{009c}/u', "œ", $result); // Fixes oe char.
	$result = preg_replace('/\x{0096}/u', "–", $result); // Fixes long dash char.
	$result = preg_replace('/\x{0093}|\x{0094}/u', '"', $result); // Fixes opening and closing double quotes.
	
	$result = html_entity_decode($result, ENT_QUOTES, "utf-8"); // TODO : remove html decode
	
	return $result;
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