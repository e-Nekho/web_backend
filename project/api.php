<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем твое готовое соединение с БД
require_once 'db.php'; 

$action = $_GET['action'] ?? '';

// 1. Проверка: авторизован ли пользователь сейчас?
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'check_auth') {
    echo json_encode([
        'is_logged_in' => isset($_SESSION['user_id']),
        'user_id' => $_SESSION['user_id'] ?? null
    ]);
    exit();
}

// 2. Действие: Вход (Login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    // Получаем JSON из тела запроса React
    $input = json_decode(file_get_contents('php://input'), true);
    $login = trim($input['login'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($login) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Ошибка базы данных.']);
    }
    exit();
}

// 3. Действие: Выход (Logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit();
}

// Читаем то, что прислал React
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Данные пусты"]);
    exit;
}

// Вытаскиваем поля из React-формы
$fullname = trim($data['name'] ?? 'Аноним');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$bio = trim($data['message'] ?? '');
$contract = !empty($data['agreement']) ? 1 : 0;

// Заглушки под структуру БД, чтобы не было ошибок NOT NULL
$birthdate = '2000-01-01';
$gender = 'male';
$login = 'user_' . time() . rand(10, 99);
$password_hash = password_hash('123456', PASSWORD_DEFAULT);

try {
    // 1. Пишем в applications
    $stmt = $pdo->prepare("
        INSERT INTO applications (login, password_hash, fullname, phone, email, birthdate, gender, bio, contract_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$login, $password_hash, $fullname, $phone, $email, $birthdate, $gender, $bio, $contract]);
    
    $application_id = $pdo->lastInsertId();
    
    // 2. Пишем в связующую таблицу языков (4 — это id для 'JavaScript' из твоего SQL-дампа)
    $stmt_lang = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, 4)
    ");
    $stmt_lang->execute([$application_id]);

    // Отдаем React ответ "Все ок"
    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}