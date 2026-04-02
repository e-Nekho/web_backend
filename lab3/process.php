<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Получение данных из формы
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $biography = trim($_POST['biography'] ?? '');
    $agreement = isset($_POST['agreement']);
    
    $errors = [];
    
    // Валидация
    if (empty($full_name) || mb_strlen($full_name) > 150 || !preg_match('/^[A-Za-zА-Яа-я\s\-]+$/u', $full_name)) {
        $errors['full_name'] = 'ФИО должно содержать только буквы и пробелы (не более 150 символов)';
    }
    
    if (empty($phone) || !preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $phone)) {
        $errors['phone'] = 'Введите корректный номер телефона';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный e-mail';
    }
    
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Укажите дату рождения';
    }
    
    $allowed_genders = ['male', 'female', 'other'];
    if (!in_array($gender, $allowed_genders)) {
        $errors['gender'] = 'Выберите пол';
    }
    
    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors['languages'] = 'Недопустимый язык';
                break;
            }
        }
    }
    
    if (!$agreement) {
        $errors['agreement'] = 'Подтвердите ознакомление с контрактом';
    }
    
    if (!empty($errors)) {
        $errors_json = urlencode(json_encode($errors));
        $query = http_build_query($_POST);
        header("Location: index.php?$query&errors=$errors_json");
        exit;
    }
    
    // Сохранение в БД
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO users (full_name, phone, email, birth_date, gender, biography, agreed) 
            VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :agreed)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':email' => $email,
        ':birth_date' => $birth_date,
        ':gender' => $gender,
        ':biography' => $biography,
        ':agreed' => $agreement ? 1 : 0
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Сохраняем языки
    $stmt_lang = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, (SELECT id FROM programming_languages WHERE name = :lang))");
    
    foreach ($languages as $lang) {
        $stmt_lang->execute([
            ':user_id' => $user_id,
            ':lang' => $lang
        ]);
    }
    
    $pdo->commit();
    header("Location: index.php?success=1");
    exit;
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    die("Ошибка базы данных. Пожалуйста, попробуйте позже.");
}
?>