<?php
// Получаем ошибки из URL
$errors = [];
if (isset($_GET['errors'])) {
    $errors = json_decode(urldecode($_GET['errors']), true) ?: [];
}

// Получаем ранее введенные значения
$old = [
    'full_name' => $_GET['full_name'] ?? '',
    'phone' => $_GET['phone'] ?? '',
    'email' => $_GET['email'] ?? '',
    'birth_date' => $_GET['birth_date'] ?? '',
    'gender' => $_GET['gender'] ?? '',
    'biography' => $_GET['biography'] ?? ''
];

$success = isset($_GET['success']) && $_GET['success'] == 1;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа 3 - Форма регистрации</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
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
        .form-content { padding: 40px; }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .required::after { content: " *"; color: #e74c3c; }
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
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .radio-group { display: flex; gap: 20px; margin-top: 8px; }
        .radio-group label { display: inline-flex; align-items: center; font-weight: normal; }
        .radio-group input[type="radio"] { margin-right: 8px; width: auto; }
        select[multiple] { height: 150px; }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        button:hover { transform: translateY(-2px); }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error-text { color: #e74c3c; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Регистрационная форма</h1>
            <p>Лабораторная работа №3</p>
        </div>
        <div class="form-content">
            <?php if ($success): ?>
                <div class="success-message">✅ Данные успешно сохранены!</div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <strong>Исправьте следующие ошибки:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label class="required">ФИО</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($old['full_name']); ?>" maxlength="150">
                </div>
                
                <div class="form-group">
                    <label class="required">Телефон</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($old['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="required">E-mail</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($old['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="required">Дата рождения</label>
                    <input type="date" name="birth_date" value="<?php echo htmlspecialchars($old['birth_date']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="required">Пол</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="male" <?php echo $old['gender'] == 'male' ? 'checked' : ''; ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" <?php echo $old['gender'] == 'female' ? 'checked' : ''; ?>> Женский</label>
                        <label><input type="radio" name="gender" value="other" <?php echo $old['gender'] == 'other' ? 'checked' : ''; ?>> Другой</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">Любимый язык программирования</label>
                    <select name="languages[]" multiple size="6">
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
                </div>
                
                <div class="form-group">
                    <label>Биография</label>
                    <textarea name="biography" rows="5"><?php echo htmlspecialchars($old['biography']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="required">
                        <input type="checkbox" name="agreement" value="1"> Я ознакомлен(а) с контрактом
                    </label>
                </div>
                
                <button type="submit">💾 Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>