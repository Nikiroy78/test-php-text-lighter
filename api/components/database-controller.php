<?php
include "config.php";
$db = new SQLite3($PATH_COMPONENTS . "/database.db");

function getRequestHistory () {
	global $db;
	
	$results = $db->query('SELECT date, hash, text FROM history;');
	$list = array();
	while ($row = $results->fetchArray()) {
		$list[] = '['. $row['date'] .']: ('. $row['hash'] .'): '. $row['text'];
	}
	
	return $list;
}

function saveHistory ($md5, $text) {
	global $db;
	
	$stmt = $db->prepare("INSERT INTO history (date, hash, text) VALUES (:time, :hash, :text);");
	//if (!$stmt) print_r("fail: " . $db->lastErrorMsg());
	$stmt->bindParam(':hash', $md5, SQLITE3_TEXT);
	$stmt->bindParam(':text', $text, SQLITE3_TEXT);
	$stmt->bindParam(':time', time(), SQLITE3_INTEGER);
	
	$stmt->execute();
}

function saveResult ($md5, $text, $result) {
	global $db;
	
	$stmt = $db->prepare("INSERT INTO results (hash, result) VALUES (:md5Val, :result);");
	//if (!$stmt) print_r("fail: " . $db->lastErrorMsg());
	$stmt->bindParam(':md5Val', $md5, SQLITE3_TEXT);
	$stmt->bindParam(':result', json_encode($result), SQLITE3_TEXT);
	
	$stmt->execute();
	
	saveHistory($md5, $text);
}

function getResultFromMd5 ($md5, $lang) {
	return "NO";
}

?>