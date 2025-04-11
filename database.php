<?php
$dbHost = 'localhost';
$dbUser = 'hnry';
$dbPass = 'hnry';
$dbName = 'hnry';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>