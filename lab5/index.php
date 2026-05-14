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


// ==========================================
// ВЫХОД
// ==========================================

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: index.php");
    exit();
}


// ==========================================
// ЗАГРУЗКА ДАННЫХ ПОЛЬЗОВАТЕЛЯ
// ==========================================

if ($is_logged_in) {

    $stmt = $pdo->prepare("
        SELECT * FROM applications
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


// ==========================================
// ВХОД
// ==========================================

if (
    isset($_POST['login_action'])
) {

    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("
        SELECT *
        FROM applications
        WHERE login = ?
    ");

    try {

    $stmt->execute([$login]);

    $user = $stmt->fetch();

}
catch (PDOException $e) {

    $errors['login'] =
        'Система авторизации недоступна. Проверьте структуру базы данных.';

    $user = false;
}

    if (
        $user &&
        password_verify($password, $user['password_hash'])
    ) {

        $_SESSION['user_id'] = $user['id'];

        header("Location: index.php");
        exit();
    }
    else {
        $errors['login'] =
            'Неверный логин или пароль.';
    }
}


// ==========================================
// СОХРАНЕНИЕ / ОБНОВЛЕНИЕ
// ==========================================

if (
    isset($_POST['save_form'])
) {

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

    // ======================================
    // ВАЛИДАЦИЯ
    // ======================================

    if (
        !preg_match(
            "/^[a-zA-Zа-яА-ЯёЁ\s\-]{1,150}$/u",
            $fullname
        )
    ) {
        $errors['fullname'] =
            'Допустимы буквы, пробелы и дефис.';
    }

    if (
        !preg_match(
            "/^\+?[0-9\s\-\(\)]{10,20}$/",
            $phone
        )
    ) {
        $errors['phone'] =
            'Допустимы цифры, пробелы, скобки и +.';
    }

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors['email'] =
            'Некорректный email.';
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
            'Выберите язык.';
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
            'Необходимо согласие.';
    }

    // ======================================
    // СОХРАНЕНИЕ
    // ======================================

    if (empty($errors)) {

        // ==================================
        // ОБНОВЛЕНИЕ
        // ==================================

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

        // ==================================
        // НОВАЯ РЕГИСТРАЦИЯ
        // ==================================

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

            $_SESSION['user_id'] = $app_id;
            $is_logged_in = true;

            $generated_credentials = [
                'login' => $login,
                'password' => $password
            ];

            $success = true;
            $is_logged_in = true;
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

</head>

<body>

<div class="container">

    <div class="header-decoration"></div>

    <h2>Регистрационная анкета</h2>

    <p class="subtitle">
        Заполните форму для участия
    </p>

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

                <span class="label-text">
                    ФИО
                </span>

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

        <button type="submit"
                name="save_form"
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

<form method="POST" class="login-form">

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
                    class="submit-btn"
                    style="margin: 0 45px 45px 45px; width: calc(100% - 90px);">

                <span class="btn-text">
                    Выйти
                </span>

            </button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>