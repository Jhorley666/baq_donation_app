<?php
//$host = 'localhost';
//$dbname = 'batquito';
//$username = 'root';
//$password = '';

$host = 'mysql.webcindario.com';
$dbname = 'alimentosquito';

$username = 'alimentosquito';
$password = '1q2w3e4r5t.';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>