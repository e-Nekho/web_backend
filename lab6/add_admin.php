<?php
require 'db.php';

// Данные админа
$login = 'admin';
$password = 'admin';  // или любой другой пароль

// Генерируем хэш
$hash = password_hash($password, PASSWORD_DEFAULT);

// Удаляем старого админа если есть
$stmt = $pdo->prepare("DELETE FROM admins WHERE login = ?");
$stmt->execute([$login]);

// Добавляем нового
$stmt = $pdo->prepare("INSERT INTO admins (login, password_hash) VALUES (?, ?)");
$stmt->execute([$login, $hash]);

echo "Админ добавлен!\n";
echo "Логин: $login\n";
echo "Хэш: $hash\n";
echo "Проверка: " . (password_verify($password, $hash) ? "OK" : "FAIL") . "\n";