<?php

require 'db.php';

function unauthorized() {

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            vertical-align: top;
        }

        th {
            background: #f4f4f4;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .delete-btn {
            background: #dc3545;
        }

        .edit-btn {
            background: #b8960c;
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