<?php

require 'common.php';

header('Content-type: application/json; charset=utf-8');

function isLoggedIn() {
	return true;
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

function formatEventDate($event) {
	return formatNb($event['jour'],2).'/'.formatNb($event['mois'],2).'/'.$event['annee'];
}

function filterOutput($value) {
	$result = utf8_encode($value);
	
	// workarounds for charset pb.
	$result = preg_replace('/\x{0080}/u', '&euro;', $result); // Fixes euro char.
	$result = preg_replace('/\x{0092}/u', "'", $result); // Fixes some apostrophes.
	$result = preg_replace('/\x{009c}/u', "&oelig;", $result); // Fixes oe char.
	$result = preg_replace('/\x{0096}/u', "–", $result); // Fixes long dash char.
	$result = preg_replace('/\x{0093}|\x{0094}/u', '"', $result); // Fixes opening and closing double quotes.
	$result = html_entity_decode($result, ENT_QUOTES, "utf-8");
	
	return $result;
}