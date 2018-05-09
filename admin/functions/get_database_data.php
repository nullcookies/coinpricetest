<?php

function showTables() {
	$arrayResult 	  =		  array();
	$db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayResult 	  =		  $db->query("SHOW TABLES FROM `teamta_bot`")->fetch_all();
	return $arrayResult;
	$db->close();
}

function getDbPlans() {
	$arrayResult 	  =		  array();
	$db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayResult 	  =		  $db->query("SELECT * FROM `plans`")->fetch_all();
	return $arrayResult;
	$db->close();
}

function getDbChiTietPlan() {
	$arrayResult 	  =		  array();
	$db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayResult 	  =		  $db->query("SELECT * FROM `chitietplan`")->fetch_all();
	return $arrayResult;
	$db->close();
}

function getDbUsers() {
	$arrayResult 	  =		  array();
	$db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayResult 	  =		  $db->query("SELECT * FROM `users`")->fetch_all();
	return $arrayResult;
	$db->close();
}
?>