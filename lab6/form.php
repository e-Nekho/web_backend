<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 5 - Авторизация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="auth-container">
        <?php if (!empty($_SESSION['login'])) : ?>
            <div class="auth-info">
                Вы вошли как: <strong><?php echo $_SESSION['login']; ?></strong> | 
                <a href="index.php?logout=1" class="logout-link">Выйти</a>
            </div>
        <?php else : ?>
            <form action="index.php" method="POST" class="auth-form">
                <input name="auth_login" class="form-inp auth-inp" placeholder="Логин">
                <input name="auth_pass" type="password" class="form-inp auth-inp" placeholder="Пароль">
                <input type="submit" name="login_btn" class="but" value="Войти">
            </form>
        <?php endif; ?>
    </header>

    <div class="form-back"> 
        <div class="form">
            <h2 class="form-title">Анкета</h2>

            <?php if (!empty($messages)) : ?>
                <div id="messages">
                    <?php foreach ($messages as $message) echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                
                <label class="form-l">ФИО:</label>
                <input name="fio" class="form-inp <?php echo ($errors['fio'] ? 'error' : ''); ?>" 
                    value="<?php echo $values['fio']; ?>" placeholder="Иванов Иван Иванович">

                <label class="form-l">Телефон:</label>
                <input name="phone" type="tel" class="form-inp <?php echo ($errors['phone'] ? 'error' : ''); ?>" 
                    value="<?php echo $values['phone']; ?>" placeholder="+7 (999) 000-00-00">

                <label class="form-l">E-mail:</label>
                <input name="mail" type="email" class="form-inp <?php echo ($errors['mail'] ? 'error' : ''); ?>" 
                    value="<?php echo $values['mail']; ?>" placeholder="email@example.com">

                <label class="form-l">Дата рождения:</label>
                <input name="birthday" type="date" class="form-inp <?php echo ($errors['birthday'] ? 'error' : ''); ?>" 
                    value="<?php echo $values['birthday']; ?>">

                <label class="form-l">Пол:</label>
                <div class="form-radio-group">
                    <label><input type="radio" name="gender" value="male" <?php if ($values['gender'] == 'male') echo 'checked'; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php if ($values['gender'] == 'female') echo 'checked'; ?>> Женский</label>
                </div>

                <label class="form-l">Любимый язык программирования:</label>
                <select name="languages[]" multiple class="form-inp <?php echo ($errors['languages'] ? 'error' : ''); ?>">
                    <option value="1" <?php if (in_array('1', $values['languages'])) echo 'selected'; ?>>Pascal</option>
                    <option value="2" <?php if (in_array('2', $values['languages'])) echo 'selected'; ?>>C</option>
                    <option value="3" <?php if (in_array('3', $values['languages'])) echo 'selected'; ?>>C++</option>
                    <option value="4" <?php if (in_array('4', $values['languages'])) echo 'selected'; ?>>JavaScript</option>
                    <option value="5" <?php if (in_array('5', $values['languages'])) echo 'selected'; ?>>PHP</option>
                    <option value="6" <?php if (in_array('6', $values['languages'])) echo 'selected'; ?>>Python</option>
                </select>

                <label class="form-l">Биография:</label>
                <textarea name="biography" class="form-inp form-textarea"><?php echo $values['biography']; ?></textarea>

                <div class="form-chek-place">
                    <input type="checkbox" name="contract" class="form-chek" value="y" 
                        <?php if ($values['contract'] == 'y') echo 'checked'; ?>>
                    <span class="form-l <?php echo ($errors['contract'] ? 'error-msg' : ''); ?>">С контрактом ознакомлен(а)</span>
                </div>

                <input type="submit" class="form-but" value="<?php echo !empty($_SESSION['login']) ? 'Обновить данные' : 'Отправить'; ?>">
            </form>
        </div>
    </div>

</body>
</html>