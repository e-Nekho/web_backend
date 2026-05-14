<?php
/**
 * Задание 6: Панель администратора.
 */

// 1. Конфигурация БД (из твоих данных)
$db_user = 'u82085';
$db_pass = '2458121';
$db_name = 'u82085';

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ERRMODE_EXCEPTION => true
    ]);
} catch (PDOException $e) {
    exit('Ошибка подключения: ' . $e->getMessage());
}

// 2. Исправление для работы HTTP-авторизации в режиме CGI на сервере
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_params = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    $_SERVER['PHP_AUTH_USER'] = $auth_params[0];
    $_SERVER['PHP_AUTH_PW'] = $auth_params[1] ?? '';
}

// 3. Проверка авторизации
if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    exit('Введите логин и пароль администратора');
}

// Проверка админа в отдельной таблице admins
$stmt = $db->prepare("SELECT pass FROM admins WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin_hash = $stmt->fetchColumn();

if (!$admin_hash || !password_verify($_SERVER['PHP_AUTH_PW'], $admin_hash)) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    exit('Доступ запрещен');
}

// 4. Логика удаления
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $db->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM application WHERE id = ?")->execute([$id]);
    header('Location: admin.php');
    exit();
}

// 5. Логика сохранения правок
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $stmt = $db->prepare("UPDATE application SET name = ?, phone = ?, email = ?, birthday = ?, gender = ?, biography = ? WHERE id = ?");
    $stmt->execute([$_POST['fio'], $_POST['phone'], $_POST['mail'], $_POST['birthday'], $_POST['gender'], $_POST['biography'], $id]);
    
    $db->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
    if (!empty($_POST['languages'])) {
        $stmt_l = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $l_id) { $stmt_l->execute([$id, $l_id]); }
    }
    header('Location: admin.php');
    exit();
}

// 6. Сбор статистики по языкам (GROUP BY)
$stats = $db->query("SELECT l.name, COUNT(al.application_id) as count FROM languages l LEFT JOIN application_languages al ON l.id = al.language_id GROUP BY l.id")->fetchAll(PDO::FETCH_ASSOC);

// 7. Получение всех данных
$users = $db->query("SELECT * FROM application")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка - Задание 6</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .stats-box { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .edit-form { margin-top: 20px; padding: 20px; border: 1px solid #1890ff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Панель администратора</h1>

        <div class="stats-box">
            <h3>Статистика по языкам:</h3>
            <ul>
                <?php foreach ($stats as $s): ?>
                    <li><strong><?php echo htmlspecialchars($s['name']); ?>:</strong> <?php echo $s['count']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <h3>Список пользователей:</h3>
        <table>
            <tr><th>ID</th><th>ФИО</th><th>Email</th><th>Действия</th></tr>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <a href="admin.php?edit_id=<?php echo $u['id']; ?>">Редактировать</a> | 
                        <a href="admin.php?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('Удалить?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['edit_id'])): 
            $id = (int)$_GET['edit_id'];
            $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
            $stmt->execute([$id]);
            $e = $stmt->fetch();
        ?>
        <div class="edit-form">
            <h2>Редактирование пользователя #<?php echo $id; ?></h2>
            <form action="admin.php" method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $id; ?>">
                ФИО: <input name="fio" value="<?php echo htmlspecialchars($e['name']); ?>" style="width:100%;"><br><br>
                Телефон: <input name="phone" value="<?php echo htmlspecialchars($e['phone']); ?>" style="width:100%;"><br><br>
                Email: <input name="mail" value="<?php echo htmlspecialchars($e['email']); ?>" style="width:100%;"><br><br>
                Дата рождения: <input name="birthday" type="date" value="<?php echo $e['birthday']; ?>"><br><br>
                Пол: 
                <select name="gender">
                    <option value="male" <?php if($e['gender']=='male') echo 'selected'; ?>>М</option>
                    <option value="female" <?php if($e['gender']=='female') echo 'selected'; ?>>Ж</option>
                </select><br><br>
                Биография: <br>
                <textarea name="biography" style="width:100%; height:100px;"><?php echo htmlspecialchars($e['biography']); ?></textarea><br><br>
                Языки программирования:<br>
                <?php
                    $all_langs = $db->query("SELECT * FROM languages")->fetchAll(PDO::FETCH_ASSOC);
                    $stmt_ul = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
                    $stmt_ul->execute([$id]);
                    $user_langs = $stmt_ul->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <select name="languages[]" multiple style="width:100%; height:120px;">
                    <?php foreach ($all_langs as $lang): ?>
                        <option value="<?php echo $lang['id']; ?>" 
                            <?php if (in_array($lang['id'], $user_langs)) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($lang['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>
                <button type="submit" style="background:#1890ff; color:white; border:none; padding:10px; cursor:pointer;">Сохранить</button>
                <a href="admin.php">Отмена</a>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>