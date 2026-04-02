<?php
require 'db.php';

$errors = [];
$success = false;
$old_data = [];

$languages = [
    "Pascal","C","C++","JavaScript","PHP","Python",
    "Java","Haskel","Clojure","Prolog","Scala","Go"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $birthdate = $_POST["birthdate"];
    $gender = $_POST["gender"] ?? '';
    $bio = trim($_POST["bio"]);
    $contract = isset($_POST["contract"]);
    $langs = $_POST["languages"] ?? [];

    // Сохраняем введенные данные для восстановления формы
    $old_data = [
        'fullname' => $fullname,
        'phone' => $phone,
        'email' => $email,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'bio' => $bio,
        'contract' => $contract,
        'languages' => $langs
    ];

    // ВАЛИДАЦИЯ
    $field_errors = [];

    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]{1,150}$/u", $fullname)) {
        $field_errors['fullname'] = "Некорректное ФИО";
    }

    if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
        $field_errors['phone'] = "Некорректный телефон";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = "Некорректный email";
    }

    if (!$birthdate) {
        $field_errors['birthdate'] = "Укажите дату рождения";
    }

    if (!in_array($gender, ['male', 'female'])) {
        $field_errors['gender'] = "Выберите пол";
    }

    if (empty($langs)) {
        $field_errors['languages'] = "Выберите хотя бы один язык";
    }

    foreach ($langs as $l) {
        if (!in_array($l, $languages)) {
            $field_errors['languages'] = "Недопустимый язык";
        }
    }

    if (!$contract) {
        $field_errors['contract'] = "Необходимо согласие с контрактом";
    }

    // СОХРАНЕНИЕ
    if (empty($field_errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO applications 
            (fullname, phone, email, birthdate, gender, bio, contract_agreed)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $fullname, $phone, $email,
            $birthdate, $gender, $bio, $contract ? 1 : 0
        ]);

        $app_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO application_languages (application_id, language_id)
            VALUES (?, (SELECT id FROM programming_languages WHERE name=?))
        ");

        foreach ($langs as $l) {
            $stmt->execute([$app_id, $l]);
        }

        $success = true;
        $old_data = []; // Очищаем данные при успешной отправке
    } else {
        $errors = $field_errors;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header-decoration"></div>
    
    <h2>Регистрационная анкета</h2>
    <p class="subtitle">Заполните форму для участия в программе</p>

    <?php if ($success): ?>
        <div class='success-message'>
            <div class="success-icon">✓</div>
            <div class="success-content">
                <strong>Успешно отправлено!</strong>
                <p>Ваши данные были сохранены. Спасибо за регистрацию.</p>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" id="mainForm">
        <!-- ФИО -->
        <div class="form-group <?php echo isset($errors['fullname']) ? 'error-group' : ''; ?>">
            <label for="fullname">
                <span class="label-text">ФИО</span>
                <span class="required">*</span>
            </label>
            <input type="text" id="fullname" name="fullname" 
                   value="<?php echo htmlspecialchars($old_data['fullname'] ?? ''); ?>" 
                   placeholder="Иванов Иван Иванович">
            <?php if (isset($errors['fullname'])): ?>
                <div class="field-error"><?php echo $errors['fullname']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Телефон -->
        <div class="form-group <?php echo isset($errors['phone']) ? 'error-group' : ''; ?>">
            <label for="phone">
                <span class="label-text">Телефон</span>
                <span class="required">*</span>
            </label>
            <input type="tel" id="phone" name="phone" 
                   value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>" 
                   placeholder="+7 123 456-78-90">
            <?php if (isset($errors['phone'])): ?>
                <div class="field-error"><?php echo $errors['phone']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group <?php echo isset($errors['email']) ? 'error-group' : ''; ?>">
            <label for="email">
                <span class="label-text">Email</span>
                <span class="required">*</span>
            </label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" 
                   placeholder="ivan@example.com">
            <?php if (isset($errors['email'])): ?>
                <div class="field-error"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Дата рождения -->
        <div class="form-group <?php echo isset($errors['birthdate']) ? 'error-group' : ''; ?>">
            <label for="birthdate">
                <span class="label-text">Дата рождения</span>
                <span class="required">*</span>
            </label>
            <input type="date" id="birthdate" name="birthdate" 
                   value="<?php echo htmlspecialchars($old_data['birthdate'] ?? ''); ?>">
            <?php if (isset($errors['birthdate'])): ?>
                <div class="field-error"><?php echo $errors['birthdate']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Пол -->
        <div class="form-group <?php echo isset($errors['gender']) ? 'error-group' : ''; ?>">
            <label>
                <span class="label-text">Пол</span>
                <span class="required">*</span>
            </label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="gender" value="male" 
                           <?php echo (($old_data['gender'] ?? '') == 'male') ? 'checked' : ''; ?>>
                    <span class="radio-custom"></span>
                    <span class="radio-text">Мужской</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="female" 
                           <?php echo (($old_data['gender'] ?? '') == 'female') ? 'checked' : ''; ?>>
                    <span class="radio-custom"></span>
                    <span class="radio-text">Женский</span>
                </label>
            </div>
            <?php if (isset($errors['gender'])): ?>
                <div class="field-error"><?php echo $errors['gender']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Языки программирования -->
        <div class="form-group <?php echo isset($errors['languages']) ? 'error-group' : ''; ?>">
            <label for="languages">
                <span class="label-text">Языки программирования</span>
                <span class="required">*</span>
            </label>
            <select name="languages[]" id="languages" multiple size="6">
                <?php foreach ($languages as $l): ?>
                    <option value="<?= htmlspecialchars($l) ?>" 
                        <?php echo (in_array($l, $old_data['languages'] ?? [])) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($l) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="hint">Для выбора нескольких языков зажмите Ctrl (Cmd)</div>
            <?php if (isset($errors['languages'])): ?>
                <div class="field-error"><?php echo $errors['languages']; ?></div>
            <?php endif; ?>
        </div>

        <!-- Биография -->
        <div class="form-group">
            <label for="bio">
                <span class="label-text">Биография</span>
            </label>
            <textarea id="bio" name="bio" rows="4" 
                      placeholder="Расскажите немного о себе..."><?php echo htmlspecialchars($old_data['bio'] ?? ''); ?></textarea>
        </div>

        <!-- Согласие с контрактом -->
        <div class="form-group <?php echo isset($errors['contract']) ? 'error-group' : ''; ?>">
            <label class="checkbox-label">
                <input type="checkbox" name="contract" value="1" 
                       <?php echo (($old_data['contract'] ?? false) || ($old_data['contract'] ?? '') === 'on') ? 'checked' : ''; ?>>
                <span class="checkbox-custom"></span>
                <span class="checkbox-text">Я ознакомлен и согласен с условиями контракта</span>
                <span class="required">*</span>
            </label>
            <?php if (isset($errors['contract'])): ?>
                <div class="field-error"><?php echo $errors['contract']; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn">
            <span class="btn-text">Отправить заявку</span>
            <span class="btn-icon">→</span>
        </button>
    </form>
</div>

<script>
// Добавляем небольшую анимацию для ошибок
document.querySelectorAll('.field-error').forEach(error => {
    error.style.animation = 'fadeInUp 0.3s ease-out';
});

// Плавная отправка формы (опционально)
document.getElementById('mainForm').addEventListener('submit', function(e) {
    const submitBtn = document.querySelector('.submit-btn');
    submitBtn.style.opacity = '0.7';
    submitBtn.disabled = true;
});
</script>

</body>
</html>