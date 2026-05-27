<?php
// Включаем отображение ошибок для быстрой отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Разрешаем запросы с любого адреса (CORS), чтобы React (на localhost:3000 или 5173) мог достучаться до PHP
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Если это предварительный запрос браузера (OPTIONS), сразу выходим
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require 'db.php'; // Подключаем твою рабочую базу данных

// Читаем JSON, который прислал React
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Данные формы не получены"]);
    exit;
}

// Принимаем то, что пришло из React-формы
$fullname = trim($data['name'] ?? 'Анонимный Пользователь');
$phone = trim($data['phone'] ?? '+70000000000');
$email = trim($data['email'] ?? 'test@example.com');
$bio = trim($data['message'] ?? 'Заявка с React-сайта');

// Заглушки для полей, требуемых в твоей БД, которых нет в форме фронтенда
$birthdate = '2000-01-01'; 
$gender = 'male';
$contract_agreed = 1;
$default_lang = 'JavaScript'; // Выберем этот язык по умолчанию для связующей таблицы

// Генерация случайного логина/пароля (взято из твоей логики в index.php)
$login = 'user' . random_int(10000, 99999);
$password = bin2hex(random_bytes(4));
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 1. Вставляем основную запись в applications
    $stmt = $pdo->prepare("
        INSERT INTO applications (login, password_hash, fullname, phone, email, birthdate, gender, bio, contract_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $login,
        $password_hash,
        $fullname,
        $phone,
        $email,
        $birthdate,
        $gender,
        $bio,
        $contract_agreed
    ]);

    // Получаем ID созданной записи
    $application_id = $pdo->lastInsertId();

    // 2. Связываем запись с языком программирования по умолчанию в application_languages
    $stmtLang = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, (SELECT id FROM programming_languages WHERE name = ? LIMIT 1))
    ");
    $stmtLang->execute([$application_id, $default_lang]);

    // Возвращаем успешный статус для React
    echo json_encode([
        "success" => true, 
        "message" => "Данные успешно улетели в базу данных!"
    ]);

} catch (PDOException $e) {
    // Если база данных выбросит ошибку, мы вернем её текст на фронтенд
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}