<?php

error_reporting(E_ALL);

$uri = $_SERVER['REQUEST_URI'];

if (!isset($_SERVER['QUERY_STRING'])) {
	if (substr($uri, -1, 1) === '?') {
		$path = substr($uri, 0, -1);
	} else {
		$path = $uri;
	}
} else {
	$query = $_SERVER['QUERY_STRING'];
	$len = strlen($query) + 1;
	$path = substr($uri, 0, -$len);
}

$file = $_SERVER['DOCUMENT_ROOT'] . $path;

if (file_exists($file)) {
	return false;
}

require_once "www/index.php";

?>
