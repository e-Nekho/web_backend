<?php
// Сохранение введенных значений и ошибок из GET параметров
$form_values = [];
$errors = [];

if (isset($_GET['full_name'])) $form_values['full_name'] = $_GET['full_name'];
if (isset($_GET['phone'])) $form_values['phone'] = $_GET['phone'];
if (isset($_GET['email'])) $form_values['email'] = $_GET['email'];
if (isset($_GET['birth_date'])) $form_values['birth_date'] = $_GET['birth_date'];
if (isset($_GET['gender'])) $form_values['gender'] = $_GET['gender'];
if (isset($_GET['biography'])) $form_values['biography'] = $_GET['biography'];

if (isset($_GET['errors'])) {
    $errors = json_decode($_GET['errors'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрационная форма</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
        }

        .form-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .required::after {
            content: " *";
            color: #e74c3c;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102,126,234,0.3);
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .radio-group label {
            display: inline-flex;
            align-items: center;
            font-weight: normal;
            margin-bottom: 0;
        }

        .radio-group input[type="radio"] {
            margin-right: 8px;
            width: auto;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: auto;
        }

        .checkbox-group label {
            margin-bottom: 0;
            font-weight: normal;
        }

        select[multiple] {
            height: 150px;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .error-text {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Регистрационная форма</h1>
            <p>Пожалуйста, заполните все поля</p>
        </div>
        <div class="form-content">
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="success-message">
                    ✅ Данные успешно сохранены! Спасибо за регистрацию.
                </div>
            <?php endif; ?>
            
            <form action="process.php" method="POST" id="registrationForm">
                <div class="form-group">
                    <label for="full_name" class="required">ФИО</label>
                    <input type="text" id="full_name" name="full_name" required 
                           maxlength="150" pattern="[A-Za-zА-Яа-я\s]+" 
                           value="<?php echo htmlspecialchars($form_values['full_name'] ?? ''); ?>">
                    <div class="error-text" id="full_name_error"></div>
                </div>

                <div class="form-group">
                    <label for="phone" class="required">Телефон</label>
                    <input type="tel" id="phone" name="phone" required 
                           placeholder="+7 (123) 456-78-90"
                           value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>">
                    <div class="error-text" id="phone_error"></div>
                </div>

                <div class="form-group">
                    <label for="email" class="required">E-mail</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                    <div class="error-text" id="email_error"></div>
                </div>

                <div class="form-group">
                    <label for="birth_date" class="required">Дата рождения</label>
                    <input type="date" id="birth_date" name="birth_date" required
                           max="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo htmlspecialchars($_GET['birth_date'] ?? ''); ?>">
                    <div class="error-text" id="birth_date_error"></div>
                </div>

                <div class="form-group">
                    <label class="required">Пол</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="male" <?php echo (($_GET['gender'] ?? '') == 'male') ? 'checked' : ''; ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" <?php echo (($_GET['gender'] ?? '') == 'female') ? 'checked' : ''; ?>> Женский</label>
                        <label><input type="radio" name="gender" value="other" <?php echo (($_GET['gender'] ?? '') == 'other') ? 'checked' : ''; ?>> Другой</label>
                    </div>
                    <div class="error-text" id="gender_error"></div>
                </div>

                <div class="form-group">
                    <label for="languages" class="required">Любимый язык программирования</label>
                    <select name="languages[]" id="languages" multiple required size="6">
                        <option value="Pascal">Pascal</option>
                        <option value="C">C</option>
                        <option value="C++">C++</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="PHP">PHP</option>
                        <option value="Python">Python</option>
                        <option value="Java">Java</option>
                        <option value="Haskel">Haskel</option>
                        <option value="Clojure">Clojure</option>
                        <option value="Prolog">Prolog</option>
                        <option value="Scala">Scala</option>
                        <option value="Go">Go</option>
                    </select>
                    <div class="error-text" id="languages_error"></div>
                </div>

                <div class="form-group">
                    <label for="biography">Биография</label>
                    <textarea id="biography" name="biography" rows="5" 
                              placeholder="Расскажите немного о себе..."><?php echo htmlspecialchars($_GET['biography'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="agreement" name="agreement" required>
                        <label for="agreement" class="required">Я ознакомлен(а) с контрактом</label>
                    </div>
                    <div class="error-text" id="agreement_error"></div>
                </div>

                <button type="submit">💾 Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>