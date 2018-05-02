<?php

include_once('config.inc.php');
include_once('Database.php');


// Create Database Object
$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);

// Enable Debugging (Optional)
$db->debug = false;
$db->show_query = false;
$db->transactionDebug = false;

//Simple Raw Query
$result = $db->query("SELECT * FROM users")->fetch_all();

echo '<pre>';
print_r($result);
echo '</pre>';

$db->close();