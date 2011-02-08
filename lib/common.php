<?php

session_start();

function __autoload($class_name) {
    require "classes/$class_name.php";
}

function openDb($dbname) {
	return new EzPDO($dbname);
}