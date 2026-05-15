<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
error_log("=== admin.php вызван ===");

require 'db.php';

function unauthorized() {
    ob_end_clean();

    header('HTTP/1.1 401 Unauthorized');

    header(
        'WWW-Authenticate: Basic realm="Admin panel"'
    );

    exit('Требуется авторизация.');
}


// =======================================
// HTTP AUTH
// =======================================

if (
    empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW'])
) {
    unauthorized();
}

$stmt = $pdo->prepare("
    SELECT *
    FROM admins
    WHERE login = ?
");

$stmt->execute([
    $_SERVER['PHP_AUTH_USER']
]);

$admin = $stmt->fetch();

if (
    !$admin ||
    !password_verify(
        $_SERVER['PHP_AUTH_PW'],
        $admin['password_hash']
    )
) {
    unauthorized();
}


// =======================================
// УДАЛЕНИЕ
// =======================================

if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("
        DELETE FROM applications
        WHERE id = ?
    ");

    $stmt->execute([
        $_GET['delete']
    ]);

    header('Location: admin.php');
    exit();
}


// =======================================
// РЕДАКТИРОВАНИЕ
// =======================================

if (isset($_POST['save_edit'])) {

    $id = $_POST['id'];

    $stmt = $pdo->prepare("
        UPDATE applications
        SET
            fullname = ?,
            phone = ?,
            email = ?,
            birthdate = ?,
            gender = ?,
            bio = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['fullname'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birthdate'],
        $_POST['gender'],
        $_POST['bio'],
        $id
    ]);

    $stmt = $pdo->prepare("
        DELETE FROM application_languages
        WHERE application_id = ?
    ");

    $stmt->execute([$id]);

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

    foreach ($_POST['languages'] as $lang) {

        $stmt->execute([$id, $lang]);
    }

    header('Location: admin.php');
    exit();
}


// =======================================
// ДАННЫЕ ПОЛЬЗОВАТЕЛЕЙ
// =======================================

$users = $pdo->query("
    SELECT *
    FROM applications
    ORDER BY id DESC
")->fetchAll();


// =======================================
// ЯЗЫКИ
// =======================================

$languages = $pdo->query("
    SELECT *
    FROM programming_languages
")->fetchAll();


// =======================================
// СТАТИСТИКА
// =======================================

$stats = $pdo->query("
    SELECT
        programming_languages.name,
        COUNT(application_languages.application_id)
        AS total

    FROM programming_languages

    LEFT JOIN application_languages
    ON programming_languages.id =
       application_languages.language_id

    GROUP BY programming_languages.id
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="ru">

<head>

    <meta charset="UTF-8">

    <title>
        Админ-панель
    </title>

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(
                135deg,
                #e8e8e8 0%,
                #c9c9c9 100%
            );

            padding: 50px 25px;
        }

        .header-decoration {
            height: 4px;

            background: linear-gradient(
                90deg,
                #b8960c 0%,
                #d4af37 50%,
                #b8960c 100%
            );
        }

        h2 {
            font-family: 'Cormorant Garamond', serif;

            font-size: 42px;

            font-weight: 600;

            text-align: center;

            margin-top: 40px;

            color: #2c2c2c;
        }

        .subtitle {
            text-align: center;

            color: #7a7a7a;

            margin-bottom: 35px;

            font-size: 14px;
        }

        .stats-box {
            margin:
                0 40px 35px 40px;

            padding: 28px;

            background:
                linear-gradient(
                    135deg,
                    #fafafa 0%,
                    #f2f2f2 100%
                );

            border:
                1px solid #e3e3e3;

            border-radius: 18px;
        }

        .stats-box h3 {
            margin-bottom: 18px;

            color: #2c2c2c;

            font-size: 22px;

            font-family:
                'Cormorant Garamond',
                serif;
        }

        .stats-box ul {
            padding-left: 18px;
        }

        .stats-box li {
            margin-bottom: 10px;

            color: #4a4a4a;
        }

        table {
            width: calc(100% - 80px);
            margin:
                0 40px 40px 40px;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border:
                1px solid #e3e3e3;
            border-radius: 18px;
            overflow: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;

            background: white;

            border-radius: 24px;

            box-shadow:
                0 20px 60px rgba(0,0,0,0.08);

            overflow: hidden;
        }

        td {
            padding: 18px 16px;
            border-bottom:
                1px solid #eeeeee;
            vertical-align: top;
            color: #4a4a4a;
        }

        th {
            background:
                linear-gradient(
                    135deg,
                    #f8f8f8 0%,
                    #eeeeee 100%
                );
            color: #2c2c2c;
            font-weight: 600;
            padding: 18px 16px;
            border-bottom:
                1px solid #dddddd;
            text-align: left;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #fcfcfc;
        }

        .admin-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .admin-btn {
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 6px 18px rgba(0,0,0,0.15);
        }

        .delete-btn {
            background:
                linear-gradient(
                    135deg,
                    #dc3545 0%,
                    #b52a38 100%
                );
        }

        .edit-btn {
            background:
                linear-gradient(
                    135deg,
                    #d4af37 0%,
                    #b8960c 100%
                );
        }

        .stats-box {
            margin-bottom: 35px;
            padding: 20px;
            background: #fafafa;
            border-radius: 16px;
        }

        .stats-box h3 {
            margin-bottom: 15px;
        }

        .edit-form textarea {
            min-height: 100px;
        }

        .edit-form {
            padding: 20px 10px 10px 10px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a4a4a;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border:
                1.5px solid #dddddd;
            border-radius: 12px;
            background: #fafafa;
            font-family: 'Montserrat', sans-serif;
            transition: 0.25s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #d4af37;
            background: white;
            box-shadow:
                0 0 0 4px rgba(212,175,55,0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 40px;
            background:
                linear-gradient(
                    135deg,
                    #4a4a4a 0%,
                    #2c2c2c 100%
                );

            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow:
                0 10px 24px rgba(0,0,0,0.15);
        }

        @media (max-width: 1000px) {

            table {
                display: block;

                overflow-x: auto;
            }

            .container {
                border-radius: 18px;
            }

            h2 {
                font-size: 34px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header-decoration"></div>

    <h2>Админ-панель</h2>

    <p class="subtitle">
        Управление пользователями
    </p>

    <!-- СТАТИСТИКА -->

    <div class="stats-box">

        <h3>
            Статистика языков
        </h3>

        <ul>

            <?php foreach ($stats as $stat): ?>

                <li>

                    <b>
                        <?= htmlspecialchars($stat['name']) ?>
                    </b>

                    :

                    <?= $stat['total'] ?>

                </li>

            <?php endforeach; ?>

        </ul>

    </div>

    <!-- ПОЛЬЗОВАТЕЛИ -->

    <table>

        <tr>

            <th>ID</th>
            <th>Логин</th>
            <th>ФИО</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Действия</th>

        </tr>

        <?php foreach ($users as $user): ?>

            <?php

            $stmt = $pdo->prepare("
                SELECT programming_languages.name
                FROM application_languages

                JOIN programming_languages
                ON application_languages.language_id =
                   programming_languages.id

                WHERE application_languages.application_id = ?
            ");

            $stmt->execute([$user['id']]);

            $user_langs =
                array_column(
                    $stmt->fetchAll(),
                    'name'
                );

            ?>

            <tr>

                <td>
                    <?= $user['id'] ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['login']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['fullname']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['email']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($user['phone']) ?>
                </td>

                <td>

                    <div class="admin-actions">

                        <a href="?edit=<?= $user['id'] ?>"
                           class="admin-btn edit-btn">

                            Редактировать

                        </a>

                        <a href="?delete=<?= $user['id'] ?>"
                           class="admin-btn delete-btn"
                           onclick="return confirm('Удалить пользователя?')">

                            Удалить

                        </a>

                    </div>

                </td>

            </tr>

            <?php if (
                isset($_GET['edit']) &&
                $_GET['edit'] == $user['id']
            ): ?>

                <tr>

                    <td colspan="6">

                        <form method="POST"
                              class="edit-form">

                            <input type="hidden"
                                   name="id"
                                   value="<?= $user['id'] ?>">

                            <div class="form-group">

                                <label>
                                    ФИО
                                </label>

                                <input type="text"
                                       name="fullname"
                                       value="<?= htmlspecialchars($user['fullname']) ?>">

                            </div>

                            <div class="form-group">

                                <label>
                                    Телефон
                                </label>

                                <input type="text"
                                       name="phone"
                                       value="<?= htmlspecialchars($user['phone']) ?>">

                            </div>

                            <div class="form-group">

                                <label>
                                    Email
                                </label>

                                <input type="email"
                                       name="email"
                                       value="<?= htmlspecialchars($user['email']) ?>">

                            </div>

                            <div class="form-group">

                                <label>
                                    Дата рождения
                                </label>

                                <input type="date"
                                       name="birthdate"
                                       value="<?= htmlspecialchars($user['birthdate']) ?>">

                            </div>

                            <div class="form-group">

                                <label>
                                    Пол
                                </label>

                                <select name="gender">

                                    <option value="male"
                                        <?= $user['gender'] == 'male' ? 'selected' : '' ?>>

                                        Мужской

                                    </option>

                                    <option value="female"
                                        <?= $user['gender'] == 'female' ? 'selected' : '' ?>>

                                        Женский

                                    </option>

                                </select>

                            </div>

                            <div class="form-group">

                                <label>
                                    Языки
                                </label>

                                <select name="languages[]"
                                        multiple
                                        size="6">

                                    <?php foreach ($languages as $lang): ?>

                                        <option value="<?= $lang['name'] ?>"
                                            <?= in_array($lang['name'], $user_langs) ? 'selected' : '' ?>>

                                            <?= htmlspecialchars($lang['name']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="form-group">

                                <label>
                                    Биография
                                </label>

                                <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

                            </div>

                            <button type="submit"
                                    name="save_edit"
                                    class="submit-btn">

                                Сохранить

                            </button>

                        </form>

                    </td>

                </tr>

            <?php endif; ?>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>