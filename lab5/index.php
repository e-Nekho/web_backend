<?php
/**
 * Реализация Задания №5: Авторизация, сессии и редактирование данных.
 */

// Включаем сессии — это основа задания 5 [cite: 3, 5]
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД (замени на свои данные)
$user = 'u82085';
$pass = '2458121';
$db_name = 'u82085';

// Устанавливаем соединение с БД один раз для всего файла
try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ERRMODE_EXCEPTION => true
    ]);
} catch (PDOException $e) {
    print('Ошибка БД: ' . $e->getMessage());
    exit();
}

// --- 1. ОБРАБОТКА GET-ЗАПРОСА ---
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();

    // Обработка выхода из аккаунта
    if (!empty($_GET['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }

    // Сообщение об успешном сохранении
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = '<div class="success-msg">Спасибо, результаты сохранены.</div>';
        
        // Если в сессии есть пароль — значит это первая регистрация, показываем его [cite: 4, 11]
        if (!empty($_SESSION['pass'])) {
            $messages[] = sprintf('<div class="info-msg">Логин: <strong>%s</strong><br>Пароль: <strong>%s</strong><br>Запишите их!</div>',
                $_SESSION['login'], $_SESSION['pass']);
            unset($_SESSION['pass']); // Показываем только один раз 
        }
    }

    // Подготовка данных для формы
    $errors = array();
    $values = array();
    
    // Поля, которые мы отслеживаем
    $fields = ['fio', 'phone', 'mail', 'birthday', 'gender', 'biography', 'languages', 'contract'];

    foreach ($fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        // Очищаем куки ошибок после считывания
        setcookie($field . '_error', '', 100000);
    }

    // Если пользователь авторизован — подгружаем его данные из БД [cite: 12]
    if (!empty($_SESSION['login'])) {
        $stmt = $db->prepare("SELECT * FROM application WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            // Если сессия есть, а в базе юзера нет — сбрасываем сессию
            session_destroy();
            header('Location: index.php');
            exit();
        }

        $values['fio'] = $row['name'];
        $values['phone'] = $row['phone'];
        $values['mail'] = $row['email'];
        $values['birthday'] = $row['birthday'];
        $values['gender'] = $row['gender'];
        $values['biography'] = $row['biography'];
        
        // Подгружаем языки
        $stmt_lang = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
        $stmt_lang->execute([$row['id']]);
        $values['languages'] = $stmt_lang->fetchAll(PDO::FETCH_COLUMN);
        $values['contract'] = 'y';
    } else {
        // Если не авторизован — берем из кук (как в задании 4) [cite: 14]
        $values['fio'] = $_COOKIE['fio_value'] ?? '';
        $values['phone'] = $_COOKIE['phone_value'] ?? '';
        $values['mail'] = $_COOKIE['mail_value'] ?? '';
        $values['birthday'] = $_COOKIE['birthday_value'] ?? '';
        $values['gender'] = $_COOKIE['gender_value'] ?? 'male';
        $values['biography'] = $_COOKIE['biography_value'] ?? '';
        $values['languages'] = !empty($_COOKIE['languages_value']) ? explode(',', $_COOKIE['languages_value']) : [];
        $values['contract'] = $_COOKIE['contract_value'] ?? '';
    }

    include('form.php');
    exit();
}

// --- 2. ОБРАБОТКА POST-ЗАПРОСА ---

// --- БЛОК АВТОРИЗАЦИИ (если нажата кнопка "Войти") ---
if (!empty($_POST['login_btn'])) {
    $login = $_POST['auth_login'];
    $pass = $_POST['auth_pass'];

    $stmt = $db->prepare("SELECT id, pass FROM application WHERE login = ?");
    $stmt->execute([$login]);
    $user_data = $stmt->fetch();

    if ($user_data && password_verify($pass, $user_data['pass'])) {
        $_SESSION['login'] = $login;
        $_SESSION['uid'] = $user_data['id'];
        header('Location: ./');
    } else {
        setcookie('auth_error', '1', time() + 3600);
        header('Location: ./');
    }
    exit();
}

// --- БЛОК ВАЛИДАЦИИ (для всех) [cite: 14] ---
$errors = FALSE;

if (empty($_POST['fio']) || !preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $_POST['fio'])) {
    setcookie('fio_error', '1', time() + 24 * 3600);
    $errors = TRUE;
}
setcookie('fio_value', $_POST['fio'], time() + 365 * 24 * 3600);

if (empty($_POST['phone']) || !preg_match('/^[\d\+\-\(\)\s]+$/', $_POST['phone'])) {
    setcookie('phone_error', '1', time() + 24 * 3600);
    $errors = TRUE;
}
setcookie('phone_value', $_POST['phone'], time() + 365 * 24 * 3600);

// ... (аналогично проверь остальные поля: email, birthday, languages, contract)

if ($errors) {
    header('Location: index.php');
    exit();
}

// --- 3. СОХРАНЕНИЕ / ОБНОВЛЕНИЕ ---

if (!empty($_SESSION['login'])) {
    // Редактирование существующей записи 
    try {
        $stmt = $db->prepare("UPDATE application SET name = ?, phone = ?, email = ?, birthday = ?, gender = ?, biography = ? WHERE login = ?");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['mail'], $_POST['birthday'], $_POST['gender'], $_POST['biography'], $_SESSION['login']]);

        // Обновляем языки (удаляем старые и пишем новые)
        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = (SELECT id FROM application WHERE login = ?)");
        $stmt->execute([$_SESSION['login']]);

        $stmt = $db->prepare("SELECT id FROM application WHERE login = ?");
        $stmt->execute([$_SESSION['login']]);
        $app_id = $stmt->fetchColumn();

        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang_id) { $stmt->execute([$app_id, $lang_id]); }
    } catch (PDOException $e) { print('Error: ' . $e->getMessage()); exit(); }

} else {
    // Генерация логина и пароля для нового пользователя [cite: 4, 11]
    $login = 'user' . time();
    $password = rand(100000, 999999);
    $pass_hash = password_hash($password, PASSWORD_DEFAULT); // В БД только хеш! 

    $_SESSION['login'] = $login;
    $_SESSION['pass'] = $password;

    try {
        $stmt = $db->prepare("INSERT INTO application (name, phone, email, birthday, gender, biography, login, pass) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['mail'], $_POST['birthday'], $_POST['gender'], $_POST['biography'], $login, $pass_hash]);

        $app_id = $db->lastInsertId();
        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang_id) { $stmt->execute([$app_id, $lang_id]); }
    } catch (PDOException $e) { print('Error: ' . $e->getMessage()); exit(); }
}

setcookie('save', '1');
header('Location: index.php');