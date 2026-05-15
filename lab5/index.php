<?php

session_start();

require 'db.php';

$languages = [
    "Pascal","C","C++","JavaScript","PHP","Python",
    "Java","Haskel","Clojure","Prolog","Scala","Go"
];

$errors = [];
$success = false;
$generated_credentials = null;

$is_logged_in = isset($_SESSION['user_id']);

$values = [
    'fullname' => '',
    'phone' => '',
    'email' => '',
    'birthdate' => '',
    'gender' => '',
    'bio' => '',
    'languages' => [],
    'contract' => false
];


// ======================================================
// ВЫХОД
// ======================================================

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: index.php");
    exit();
}


// ======================================================
// ЗАГРУЗКА ДАННЫХ ПОЛЬЗОВАТЕЛЯ
// ======================================================

if ($is_logged_in) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM applications
        WHERE id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch();

    if ($user) {

        $values['fullname'] = $user['fullname'];
        $values['phone'] = $user['phone'];
        $values['email'] = $user['email'];
        $values['birthdate'] = $user['birthdate'];
        $values['gender'] = $user['gender'];
        $values['bio'] = $user['bio'];
        $values['contract'] = $user['contract_agreed'];

        $stmt = $pdo->prepare("
            SELECT programming_languages.name
            FROM application_languages
            JOIN programming_languages
            ON application_languages.language_id = programming_languages.id
            WHERE application_languages.application_id = ?
        ");

        $stmt->execute([$_SESSION['user_id']]);

        $values['languages'] =
            array_column($stmt->fetchAll(), 'name');
    }
}


// ======================================================
// ВХОД
// ======================================================

if (isset($_POST['login_action'])) {

    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {

        $stmt = $pdo->prepare("
            SELECT *
            FROM applications
            WHERE login = ?
        ");

        $stmt->execute([$login]);

        $user = $stmt->fetch();

        if (
            $user &&
            password_verify($password, $user['password_hash'])
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];

            header("Location: index.php");
            exit();
        }
        else {

            $errors['login'] =
                'Неверный логин или пароль.';
        }
    }
    catch (PDOException $e) {

        $errors['login'] =
            'Ошибка структуры базы данных. Проверьте schema.sql.';
    }
}


// ======================================================
// РЕГИСТРАЦИЯ / ОБНОВЛЕНИЕ
// ======================================================

if (isset($_POST['save_form'])) {

    $fullname = trim($_POST["fullname"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $birthdate = $_POST["birthdate"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $bio = trim($_POST["bio"] ?? '');
    $contract = isset($_POST["contract"]);
    $langs = $_POST["languages"] ?? [];

    $values = [
        'fullname' => $fullname,
        'phone' => $phone,
        'email' => $email,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'bio' => $bio,
        'languages' => $langs,
        'contract' => $contract
    ];

    // ==================================================
    // ВАЛИДАЦИЯ
    // ==================================================

    if (
        empty($fullname) ||
        !preg_match(
            "/^[a-zA-Zа-яА-ЯёЁ\s\-]{1,150}$/u",
            $fullname
        )
    ) {
        $errors['fullname'] =
            'Допустимы только буквы, пробелы и дефис.';
    }

    if (
        empty($phone) ||
        !preg_match(
            "/^\+?[0-9\s\-\(\)]{10,20}$/",
            $phone
        )
    ) {
        $errors['phone'] =
            'Допустимы цифры, пробелы, скобки и знак +.';
    }

    if (
        empty($email) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors['email'] =
            'Введите корректный email.';
    }

    if (!$birthdate) {

        $errors['birthdate'] =
            'Укажите дату рождения.';
    }

    if (
        !in_array($gender, ['male', 'female'])
    ) {
        $errors['gender'] =
            'Выберите пол.';
    }

    if (empty($langs)) {

        $errors['languages'] =
            'Выберите минимум один язык.';
    }

    if (
        !preg_match(
            "/^[a-zA-Zа-яА-ЯёЁ0-9\s.,!?()\-\"']*$/u",
            $bio
        )
    ) {
        $errors['bio'] =
            'Биография содержит недопустимые символы.';
    }

    if (!$contract) {

        $errors['contract'] =
            'Необходимо согласие с контрактом.';
    }

    // ==================================================
    // СОХРАНЕНИЕ
    // ==================================================

    if (empty($errors)) {

        // ==============================================
        // ОБНОВЛЕНИЕ
        // ==============================================

        if ($is_logged_in) {

            $stmt = $pdo->prepare("
                UPDATE applications
                SET
                    fullname = ?,
                    phone = ?,
                    email = ?,
                    birthdate = ?,
                    gender = ?,
                    bio = ?,
                    contract_agreed = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $fullname,
                $phone,
                $email,
                $birthdate,
                $gender,
                $bio,
                $contract ? 1 : 0,
                $_SESSION['user_id']
            ]);

            $stmt = $pdo->prepare("
                DELETE FROM application_languages
                WHERE application_id = ?
            ");

            $stmt->execute([
                $_SESSION['user_id']
            ]);

            $stmt = $pdo->prepare("
                INSERT INTO application_languages
                (application_id, language_id)
                VALUES (
                    ?,
                    (
                        SELECT id
                        FROM programming_languages
                        WHERE name = ?
                    )
                )
            ");

            foreach ($langs as $lang) {

                $stmt->execute([
                    $_SESSION['user_id'],
                    $lang
                ]);
            }

            $success = true;
        }

        // ==============================================
        // НОВАЯ РЕГИСТРАЦИЯ
        // ==============================================

        else {

            $login =
                'user' .
                random_int(10000, 99999);

            $password =
                bin2hex(random_bytes(4));

            $password_hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt = $pdo->prepare("
                INSERT INTO applications
                (
                    login,
                    password_hash,
                    fullname,
                    phone,
                    email,
                    birthdate,
                    gender,
                    bio,
                    contract_agreed
                )
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
                $contract ? 1 : 0
            ]);

            $app_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO application_languages
                (application_id, language_id)
                VALUES (
                    ?,
                    (
                        SELECT id
                        FROM programming_languages
                        WHERE name = ?
                    )
                )
            ");

            foreach ($langs as $lang) {

                $stmt->execute([
                    $app_id,
                    $lang
                ]);
            }

            session_regenerate_id(true);

            $_SESSION['user_id'] = $app_id;

            $is_logged_in = true;

            $generated_credentials = [
                'login' => $login,
                'password' => $password
            ];

            $success = true;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>

    <meta charset="UTF-8">

    <title>
        Регистрационная анкета
    </title>

    <link rel="stylesheet" href="style.css">

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;500;600&display=swap"
          rel="stylesheet">

    <style>

        .hint-box {
            margin: 0 45px 25px 45px;
            padding: 18px 20px;
            background: linear-gradient(
                135deg,
                #fff8e7 0%,
                #fff3cd 100%
            );
            border-left: 4px solid #d4af37;
            border-radius: 14px;
        }

        .hint-box strong {
            display: block;
            margin-bottom: 8px;
            color: #7a5d00;
            font-size: 15px;
        }

        .hint-box p {
            color: #5f5f5f;
            font-size: 13px;
            line-height: 1.5;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;

            margin: 10px 45px 25px 45px;

            color: #888;
            font-size: 13px;
            white-space: nowrap;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ddd;
        }

        .logout-btn {
            margin: 0 45px 45px 45px;
            width: calc(100% - 90px);
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header-decoration"></div>

    <h2>Регистрационная анкета</h2>

    <p class="subtitle">
        Заполните форму для участия
    </p>

    <div class="hint-box">

        <strong>
            Как это работает?
        </strong>

        <p>
            Сначала заполните и отправьте анкету.
            После успешной регистрации система автоматически
            сгенерирует логин и пароль для последующего
            входа и редактирования данных.
        </p>

    </div>

    <?php if ($success): ?>

        <div class="success-message">

            <div class="success-icon">
                ✓
            </div>

            <div class="success-content">

                <strong>
                    Данные успешно сохранены
                </strong>

                <?php if ($generated_credentials): ?>

                    <p>
                        Ваш логин:
                        <b>
                            <?= htmlspecialchars($generated_credentials['login']) ?>
                        </b>
                    </p>

                    <p>
                        Ваш пароль:
                        <b>
                            <?= htmlspecialchars($generated_credentials['password']) ?>
                        </b>
                    </p>

                <?php else: ?>

                    <p>
                        Изменения успешно обновлены.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

    <form method="POST">

        <!-- ФИО -->
        <div class="form-group <?= isset($errors['fullname']) ? 'error-group' : '' ?>">

            <label for="fullname">
                <span class="label-text">ФИО</span>
                <span class="required">*</span>
            </label>

            <input type="text"
                   id="fullname"
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

            <label for="phone">
                <span class="label-text">Телефон</span>
                <span class="required">*</span>
            </label>

            <input type="text"
                   id="phone"
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

            <label for="email">
                <span class="label-text">Email</span>
                <span class="required">*</span>
            </label>

            <input type="email"
                   id="email"
                   name="email"
                   value="<?= htmlspecialchars($values['email']) ?>">

            <?php if (isset($errors['email'])): ?>
                <div class="field-error">
                    <?= $errors['email'] ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Дата рождения -->
        <div class="form-group <?= isset($errors['birthdate']) ? 'error-group' : '' ?>">

            <label for="birthdate">
                <span class="label-text">Дата рождения</span>
                <span class="required">*</span>
            </label>

            <input type="date"
                   id="birthdate"
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

            <label>
                <span class="label-text">Пол</span>
                <span class="required">*</span>
            </label>

            <div class="radio-group">

                <label class="radio-label">

                    <input type="radio"
                           name="gender"
                           value="male"
                        <?= $values['gender'] === 'male' ? 'checked' : '' ?>>

                    <span class="radio-custom"></span>

                    <span class="radio-text">
                        Мужской
                    </span>

                </label>

                <label class="radio-label">

                    <input type="radio"
                           name="gender"
                           value="female"
                        <?= $values['gender'] === 'female' ? 'checked' : '' ?>>

                    <span class="radio-custom"></span>

                    <span class="radio-text">
                        Женский
                    </span>

                </label>

            </div>

            <?php if (isset($errors['gender'])): ?>
                <div class="field-error">
                    <?= $errors['gender'] ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Языки -->
        <div class="form-group <?= isset($errors['languages']) ? 'error-group' : '' ?>">

            <label for="languages">
                <span class="label-text">
                    Языки программирования
                </span>
                <span class="required">*</span>
            </label>

            <select name="languages[]"
                    id="languages"
                    multiple
                    size="6">

                <?php foreach ($languages as $lang): ?>

                    <option value="<?= htmlspecialchars($lang) ?>"
                        <?= in_array($lang, $values['languages']) ? 'selected' : '' ?>>

                        <?= htmlspecialchars($lang) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <div class="hint">
                Для выбора нескольких языков используйте Ctrl или Cmd
            </div>

            <?php if (isset($errors['languages'])): ?>
                <div class="field-error">
                    <?= $errors['languages'] ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Биография -->
        <div class="form-group <?= isset($errors['bio']) ? 'error-group' : '' ?>">

            <label for="bio">
                <span class="label-text">Биография</span>
            </label>

            <textarea id="bio"
                      name="bio"
                      rows="4"><?= htmlspecialchars($values['bio']) ?></textarea>

            <?php if (isset($errors['bio'])): ?>
                <div class="field-error">
                    <?= $errors['bio'] ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Контракт -->
        <div class="form-group <?= isset($errors['contract']) ? 'error-group' : '' ?>">

            <label class="checkbox-label">

                <input type="checkbox"
                       name="contract"
                       value="1"
                    <?= $values['contract'] ? 'checked' : '' ?>>

                <span class="checkbox-custom"></span>

                <span class="checkbox-text">
                    Я ознакомлен и согласен с условиями контракта
                </span>

                <span class="required">*</span>

            </label>

            <?php if (isset($errors['contract'])): ?>
                <div class="field-error">
                    <?= $errors['contract'] ?>
                </div>
            <?php endif; ?>

        </div>

        <button type="submit"
                name="save_form"
                value="1"
                class="submit-btn">

            <span class="btn-text">

                <?= $is_logged_in
                    ? 'Сохранить изменения'
                    : 'Отправить заявку'
                ?>

            </span>

            <span class="btn-icon">
                →
            </span>

        </button>

    </form>

    <div class="auth-divider">
        Уже зарегистрированы?
    </div>

    <form method="POST">

        <div class="form-group <?= isset($errors['login']) ? 'error-group' : '' ?>">

            <label>
                <span class="label-text">
                    Логин
                </span>
            </label>

            <input type="text"
                   name="login">

        </div>

        <div class="form-group <?= isset($errors['login']) ? 'error-group' : '' ?>">

            <label>
                <span class="label-text">
                    Пароль
                </span>
            </label>

            <input type="password"
                   name="password">

            <?php if (isset($errors['login'])): ?>

                <div class="field-error">
                    <?= $errors['login'] ?>
                </div>

            <?php endif; ?>

        </div>

        <button type="submit"
                name="login_action"
                value="1"
                class="submit-btn">

            <span class="btn-text">
                Войти
            </span>

        </button>

    </form>

    <?php if ($is_logged_in): ?>

        <form method="GET">

            <button type="submit"
                    name="logout"
                    value="1"
                    class="submit-btn logout-btn">

                <span class="btn-text">
                    Выйти
                </span>

            </button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>