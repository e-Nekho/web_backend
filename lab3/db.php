<?php
$dsn = 'mysql:host=localhost;dbname=YOUR_DB;charset=utf8';
$user = 'YOUR_USER';
$pass = 'YOUR_PASSWORD';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}