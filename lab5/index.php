<?php

session_start();

require 'db.php';

$languages = [
    "Pascal","C","C++","JavaScript","PHP","Python",
    "Java","Haskel","Clojure","Prolog","Scala","Go"
];

$form_fields = [
    'fullname',
    'phone',
    'email',
    'birthdate',
    'gender',
    'bio',
    'languages',
    'contract'
];

$errors = [];
$success = false;
$generated_credentials = null;

$is_logged_in = isset($_SESSION['user_id']);


// ======================================================
// ЗАГРУЗКА VALUES ИЗ COOKIES
// ======================================================

$values = [
    'fullname' => $_COOKIE['fullname_value'] ?? '',
    'phone' => $_COOKIE['phone_value'] ?? '',
    'email' => $_COOKIE['email_value'] ?? '',
    'birthdate' => $_COOKIE['birthdate_value'] ?? '',
    'gender' => $_COOKIE['gender_value'] ?? '',
    'bio' => $_COOKIE['bio_value'] ?? '',
    'languages' => isset($_COOKIE['languages_value'])
        ? json_decode($_COOKIE['languages_value'], true)
        : [],
    'contract' => $_COOKIE['contract_value'] ?? false
];


// ======================================================
// ЗАГРУЗКА ОШИБОК И ИХ УДАЛЕНИЕ
// ======================================================

foreach ($form_fields as $field) {

    if (!empty($_COOKIE[$field . '_error'])) {

        $errors[$field] =
            $_COOKIE[$field . '_error'];

        setcookie(
            $field . '_error',
            '',
            time() - 3600,
            '/'
        );
    }
}


// ======================================================
// ВЫХОД
// ======================================================

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: index.php");
    exit();
}


// ======================================================
// ЗАГРУЗКА ДАННЫХ АВТОРИЗОВАННОГО ПОЛЬЗОВАТЕЛЯ
// ======================================================

if ($is_logged_in) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM applications
        WHERE id = ?
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);

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
            ON application_languages.language_id =
               programming_languages.id

            WHERE application_languages.application_id = ?
        ");

        $stmt->execute([
            $_SESSION['user_id']
        ]);

        $values['languages'] =
            array_column(
                $stmt->fetchAll(),
                'name'
            );
    }
}


// ======================================================
// ВХОД
// ======================================================

if (isset($_POST['login_action'])) {

    $login =
        trim($_POST['login'] ?? '');

    $password =
        trim($_POST['password'] ?? '');

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
            password_verify(
                $password,
                $user['password_hash']
            )
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] =
                $user['id'];

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
            'Ошибка структуры базы данных.';
    }
}


// ======================================================
// СОХРАНЕНИЕ ФОРМЫ
// ======================================================

if (isset($_POST['save_form'])) {

    $fullname =
        trim($_POST["fullname"] ?? '');

    $phone =
        trim($_POST["phone"] ?? '');

    $email =
        trim($_POST["email"] ?? '');

    $birthdate =
        $_POST["birthdate"] ?? '';

    $gender =
        $_POST["gender"] ?? '';

    $bio =
        trim($_POST["bio"] ?? '');

    $contract =
        isset($_POST["contract"]);

    $langs =
        $_POST["languages"] ?? [];


    // ==================================================
    // VALUES
    // ==================================================

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
    // СОХРАНЕНИЕ VALUES В COOKIES
    // ==================================================

    $cookie_time =
        time() + 60 * 60 * 24 * 365;

    setcookie(
        'fullname_value',
        $fullname,
        $cookie_time,
        '/'
    );

    setcookie(
        'phone_value',
        $phone,
        $cookie_time,
        '/'
    );

    setcookie(
        'email_value',
        $email,
        $cookie_time,
        '/'
    );

    setcookie(
        'birthdate_value',
        $birthdate,
        $cookie_time,
        '/'
    );

    setcookie(
        'gender_value',
        $gender,
        $cookie_time,
        '/'
    );

    setcookie(
        'bio_value',
        $bio,
        $cookie_time,
        '/'
    );

    setcookie(
        'languages_value',
        json_encode($langs),
        $cookie_time,
        '/'
    );

    setcookie(
        'contract_value',
        $contract ? '1' : '',
        $cookie_time,
        '/'
    );


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
            'Допустимы цифры, пробелы, скобки и +.';
    }

    if (
        empty($email) ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors['email'] =
            'Введите корректный email.';
    }

    if (!$birthdate) {

        $errors['birthdate'] =
            'Укажите дату рождения.';
    }

    if (
        !in_array(
            $gender,
            ['male', 'female']
        )
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
    // СОХРАНЕНИЕ ОШИБОК В COOKIES
    // ==================================================

    if (!empty($errors)) {

        foreach ($errors as $field => $message) {

            setcookie(
                $field . '_error',
                $message,
                0,
                '/'
            );
        }

        header("Location: index.php");
        exit();
    }


    // ==================================================
    // ОЧИСТКА ОШИБОК
    // ==================================================

    foreach ($form_fields as $field) {

        setcookie(
            $field . '_error',
            '',
            time() - 3600,
            '/'
        );
    }


    // ==================================================
    // ОБНОВЛЕНИЕ
    // ==================================================

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

    // ==================================================
    // НОВАЯ РЕГИСТРАЦИЯ
    // ==================================================

    else {

        $login =
            'user' .
            random_int(10000, 99999);

        $password =
            bin2hex(
                random_bytes(4)
            );

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

        $app_id =
            $pdo->lastInsertId();

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

        $_SESSION['user_id'] =
            $app_id;

        $is_logged_in = true;

        $generated_credentials = [
            'login' => $login,
            'password' => $password
        ];

        $success = true;
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
            сгенерирует логин и пароль для входа
            и редактирования данных.
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

    <!-- ДАЛЕЕ ИДЁТ HTML ФОРМЫ ИЗ ПРЕДЫДУЩЕЙ ВЕРСИИ -->
    <!-- ОН ОСТАЁТСЯ БЕЗ ИЗМЕНЕНИЙ -->

</div>

</body>
</html>