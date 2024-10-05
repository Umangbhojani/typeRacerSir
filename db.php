<?php
// db.php

date_default_timezone_set('Asia/Kolkata');

$host = 'localhost'; // Change as needed
$dbname = 'typeRacer'; // Your database name
$username = 'root'; // Your database username
$password = ''; // Your database password

$project_url = 'http://localhost/typeracersir/';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
