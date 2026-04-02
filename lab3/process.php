<?php
// Настройки подключения к БД
$host = 'localhost';
$dbname = 'form_db';
$username = 'root'; // Измените на вашего пользователя
$password = '';     // Измените на ваш пароль

try {
    // Подключение к БД
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    // 1. Валидация ФИО
    if (empty($full_name)) {
        $errors['full_name'] = 'ФИО обязательно для заполнения';
    } elseif (mb_strlen($full_name) > 150) {
        $errors['full_name'] = 'ФИО не должно превышать 150 символов';
    } elseif (!preg_match('/^[A-Za-zА-Яа-я\s\-]+$/u', $full_name)) {
        $errors['full_name'] = 'ФИО должно содержать только буквы, пробелы и дефисы';
    }
    
    // 2. Валидация телефона
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^[\+0-9\s\-\(\)]{10,20}$/', $phone)) {
        $errors['phone'] = 'Введите корректный номер телефона';
    }
    
    // 3. Валидация email
    if (empty($email)) {
        $errors['email'] = 'E-mail обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный e-mail адрес';
    }
    
    // 4. Валидация даты рождения
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Дата рождения обязательна';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$date || $date->format('Y-m-d') !== $birth_date) {
            $errors['birth_date'] = 'Неверный формат даты';
        } elseif ($date > new DateTime()) {
            $errors['birth_date'] = 'Дата рождения не может быть в будущем';
        }
    }
    
    // 5. Валидация пола
    $allowed_genders = ['male', 'female', 'other'];
    if (empty($gender)) {
        $errors['gender'] = 'Выберите пол';
    } elseif (!in_array($gender, $allowed_genders)) {
        $errors['gender'] = 'Недопустимое значение пола';
    }
    
    // 6. Валидация языков программирования
    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors['languages'] = 'Выбран недопустимый язык программирования';
                break;
            }
        }
    }
    
    // 7. Валидация биографии (не обязательна, но можно ограничить длину)
    if (strlen($biography) > 5000) {
        $errors['biography'] = 'Биография не должна превышать 5000 символов';
    }
    
    // 8. Валидация чекбокса согласия
    if (!$agreement) {
        $errors['agreement'] = 'Вы должны ознакомиться с контрактом';
    }
    
    // Если есть ошибки - возвращаем на форму с сообщениями
    if (!empty($errors)) {
        $query_params = http_build_query(array_merge($_POST, ['errors' => $errors]));
        $errors_json = urlencode(json_encode($errors));
        header("Location: index.html?" . http_build_query($_POST) . "&errors=" . $errors_json);
        exit;
    }
    
    // Использование подготовленных запросов (prepared statements)
    
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    try {
        // Вставка данных пользователя
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
        
        // Вставка выбранных языков программирования
        $sql_lang = "INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, :language_id)";
        $stmt_lang = $pdo->prepare($sql_lang);
        
        // Получаем ID языков из справочника
        $sql_get_lang_id = "SELECT id FROM programming_languages WHERE name = :name";
        $stmt_get_lang = $pdo->prepare($sql_get_lang_id);
        
        foreach ($languages as $lang_name) {
            $stmt_get_lang->execute([':name' => $lang_name]);
            $lang_id = $stmt_get_lang->fetchColumn();
            
            if ($lang_id) {
                $stmt_lang->execute([
                    ':user_id' => $user_id,
                    ':language_id' => $lang_id
                ]);
            }
        }
        
        // Фиксируем транзакцию
        $pdo->commit();
        
        // Перенаправление с сообщением об успехе
        header("Location: /index.html?success=1");
        exit;
        
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>