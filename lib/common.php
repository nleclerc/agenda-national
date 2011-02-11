<?php

function __autoload($class_name) {
    require "classes/$class_name.php";
}

function filterOutput($value) {
	$result = utf8_encode($value);
	
	// workarounds for charset pb.
	$result = preg_replace('/\x{0080}/u', '€', $result); // Fixes euro char.
	$result = preg_replace('/\x{0092}/u', "'", $result); // Fixes some apostrophes.
	$result = preg_replace('/\x{009c}/u', "œ", $result); // Fixes oe char.
	$result = preg_replace('/\x{0096}/u', "–", $result); // Fixes long dash char.
	$result = preg_replace('/\x{0093}|\x{0094}/u', '"', $result); // Fixes opening and closing double quotes.
	
	$result = html_entity_decode($result, ENT_QUOTES, "utf-8"); // TODO : remove html decode
	
	return $result;
}