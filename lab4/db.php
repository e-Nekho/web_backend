<?php
$dsn = 'mysql:host=localhost;dbname=u82084;charset=utf8';
$user = 'u82084';
$pass = '9947124';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}