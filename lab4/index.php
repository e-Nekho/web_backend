<?php
require 'db.php';

$languages = [
    "Pascal","C","C++","JavaScript","PHP","Python",
    "Java","Haskel","Clojure","Prolog","Scala","Go"
];

$errors = [];
$values = [];

// =======================
// ЗАГРУЗКА ДАННЫХ ИЗ COOKIES
// =======================

$fields = [
    'fullname',
    'phone',
    'email',
    'birthdate',
    'gender',
    'bio'
];

foreach ($fields as $field) {
    $values[$field] = $_COOKIE[$field . '_value'] ?? '';
}

$values['languages'] = isset($_COOKIE['languages_value'])
    ? json_decode($_COOKIE['languages_value'], true)
    : [];

$values['contract'] = $_COOKIE['contract_value'] ?? '';

// Ошибки
foreach ($fields as $field) {
    if (!empty($_COOKIE[$field . '_error'])) {
        $errors[$field] = $_COOKIE[$field . '_error'];

        setcookie($field . '_error', '', time() - 3600);
    }
}

if (!empty($_COOKIE['languages_error'])) {
    $errors['languages'] = $_COOKIE['languages_error'];
    setcookie('languages_error', '', time() - 3600);
}

if (!empty($_COOKIE['contract_error'])) {
    $errors['contract'] = $_COOKIE['contract_error'];
    setcookie('contract_error', '', time() - 3600);
}

$success = !empty($_COOKIE['success']);

if ($success) {
    setcookie('success', '', time() - 3600);
}

// =======================
// POST ОБРАБОТКА
// =======================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $birthdate = $_POST["birthdate"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $bio = trim($_POST["bio"] ?? '');
    $contract = isset($_POST["contract"]);
    $langs = $_POST["languages"] ?? [];

    $field_errors = [];

    // =======================
    // ВАЛИДАЦИЯ
    // =======================

    // ФИО
    if (empty($fullname)) {
        $field_errors['fullname'] = 'Поле ФИО обязательно.';
    }
    elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]{1,150}$/u", $fullname)) {
        $field_errors['fullname'] =
            'Допустимы только буквы, пробелы и дефис.';
    }

    // Телефон
    if (empty($phone)) {
        $field_errors['phone'] = 'Поле телефона обязательно.';
    }
    elseif (!preg_match("/^\+?[0-9\s\-\(\)]{10,20}$/", $phone)) {
        $field_errors['phone'] =
            'Допустимы цифры, пробелы, скобки, дефис и знак +.';
    }

    // Email
    if (empty($email)) {
        $field_errors['email'] = 'Поле email обязательно.';
    }
    elseif (!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/", $email)) {
        $field_errors['email'] =
            'Введите корректный email.';
    }

    // Дата
    if (empty($birthdate)) {
        $field_errors['birthdate'] = 'Укажите дату рождения.';
    }

    // Пол
    if (!in_array($gender, ['male', 'female'])) {
        $field_errors['gender'] = 'Выберите пол.';
    }

    // Языки
    if (empty($langs)) {
        $field_errors['languages'] =
            'Выберите хотя бы один язык.';
    } else {
        foreach ($langs as $lang) {
            if (!in_array($lang, $languages)) {
                $field_errors['languages'] =
                    'Выбран недопустимый язык.';
                break;
            }
        }
    }

    // Биография
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9\s.,!?()\-\"']*$/u", $bio)) {
        $field_errors['bio'] =
            'Биография содержит недопустимые символы.';
    }

    // Контракт
    if (!$contract) {
        $field_errors['contract'] =
            'Необходимо согласие с контрактом.';
    }

    // =======================
    // СОХРАНЕНИЕ VALUES В COOKIE
    // =======================

    $cookie_time = time() + 60 * 60 * 24 * 365;

    setcookie('fullname_value', $fullname, $cookie_time);
    setcookie('phone_value', $phone, $cookie_time);
    setcookie('email_value', $email, $cookie_time);
    setcookie('birthdate_value', $birthdate, $cookie_time);
    setcookie('gender_value', $gender, $cookie_time);
    setcookie('bio_value', $bio, $cookie_time);
    setcookie('languages_value', json_encode($langs), $cookie_time);
    setcookie('contract_value', $contract ? '1' : '', $cookie_time);

    // =======================
    // ЕСЛИ ЕСТЬ ОШИБКИ
    // =======================

    if (!empty($field_errors)) {

        foreach ($field_errors as $field => $message) {
            setcookie($field . '_error', $message);
        }

        header('Location: index.php');
        exit();
    }

    // =======================
    // СОХРАНЕНИЕ В БАЗУ
    // =======================

    $stmt = $pdo->prepare("
        INSERT INTO applications
        (fullname, phone, email, birthdate, gender, bio, contract_agreed)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fullname,
        $phone,
        $email,
        $birthdate,
        $gender,
        $bio,
        $contract ? 1 : 0
    ]);

    $app_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO application_languages
        (application_id, language_id)
        VALUES (?, (SELECT id FROM programming_languages WHERE name=?))
    ");

    foreach ($langs as $lang) {
        $stmt->execute([$app_id, $lang]);
    }

    setcookie('success', '1');

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .error-group input,
        .error-group textarea,
        .error-group select {
            border: 2px solid red;
        }

        .field-error {
            color: red;
            margin-top: 5px;
            font-size: 14px;
        }

        .success-message {
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Регистрационная анкета</h2>

    <?php if ($success): ?>
        <div class="success-message">
            Данные успешно сохранены.
        </div>
    <?php endif; ?>

    <form method="POST">

        <!-- ФИО -->
        <div class="form-group <?= isset($errors['fullname']) ? 'error-group' : '' ?>">
            <label>ФИО *</label>

            <input type="text"
                   name="fullname"
                   value="<?= htmlspecialchars($values['fullname']) ?>">

            <?php if (isset($errors['fullname'])): ?>
                <div class="field-error">
                    <?= $errors['fullname'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Телефон -->
        <div class="form-group <?= isset($errors['phone']) ? 'error-group' : '' ?>">
            <label>Телефон *</label>

            <input type="text"
                   name="phone"
                   value="<?= htmlspecialchars($values['phone']) ?>">

            <?php if (isset($errors['phone'])): ?>
                <div class="field-error">
                    <?= $errors['phone'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group <?= isset($errors['email']) ? 'error-group' : '' ?>">
            <label>Email *</label>

            <input type="text"
                   name="email"
                   value="<?= htmlspecialchars($values['email']) ?>">

            <?php if (isset($errors['email'])): ?>
                <div class="field-error">
                    <?= $errors['email'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Дата -->
        <div class="form-group <?= isset($errors['birthdate']) ? 'error-group' : '' ?>">
            <label>Дата рождения *</label>

            <input type="date"
                   name="birthdate"
                   value="<?= htmlspecialchars($values['birthdate']) ?>">

            <?php if (isset($errors['birthdate'])): ?>
                <div class="field-error">
                    <?= $errors['birthdate'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Пол -->
        <div class="form-group <?= isset($errors['gender']) ? 'error-group' : '' ?>">
            <label>Пол *</label>

            <input type="radio"
                   name="gender"
                   value="male"
                <?= $values['gender'] === 'male' ? 'checked' : '' ?>>
            Мужской

            <input type="radio"
                   name="gender"
                   value="female"
                <?= $values['gender'] === 'female' ? 'checked' : '' ?>>
            Женский

            <?php if (isset($errors['gender'])): ?>
                <div class="field-error">
                    <?= $errors['gender'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Языки -->
        <div class="form-group <?= isset($errors['languages']) ? 'error-group' : '' ?>">
            <label>Языки программирования *</label>

            <select name="languages[]" multiple size="6">

                <?php foreach ($languages as $lang): ?>

                    <option value="<?= htmlspecialchars($lang) ?>"
                        <?= in_array($lang, $values['languages']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang) ?>
                    </option>

                <?php endforeach; ?>

            </select>

            <?php if (isset($errors['languages'])): ?>
                <div class="field-error">
                    <?= $errors['languages'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Биография -->
        <div class="form-group <?= isset($errors['bio']) ? 'error-group' : '' ?>">
            <label>Биография</label>

            <textarea name="bio"><?= htmlspecialchars($values['bio']) ?></textarea>

            <?php if (isset($errors['bio'])): ?>
                <div class="field-error">
                    <?= $errors['bio'] ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Контракт -->
        <div class="form-group <?= isset($errors['contract']) ? 'error-group' : '' ?>">

            <label>
                <input type="checkbox"
                       name="contract"
                       value="1"
                    <?= $values['contract'] ? 'checked' : '' ?>>

                Согласен с контрактом *
            </label>

            <?php if (isset($errors['contract'])): ?>
                <div class="field-error">
                    <?= $errors['contract'] ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit">
            Отправить
        </button>

    </form>

</div>

</body>
</html>