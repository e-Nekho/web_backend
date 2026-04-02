<?php
require 'db.php';

$errors = [];
$success = false;

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

    // 🔎 ВАЛИДАЦИЯ

    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]{1,150}$/u", $fullname)) {
        $errors[] = "Некорректное ФИО";
    }

    if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
        $errors[] = "Некорректный телефон";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }

    if (!$birthdate) {
        $errors[] = "Укажите дату рождения";
    }

    if (!in_array($gender, ['male', 'female'])) {
        $errors[] = "Выберите пол";
    }

    if (empty($langs)) {
        $errors[] = "Выберите хотя бы один язык";
    }

    foreach ($langs as $l) {
        if (!in_array($l, $languages)) {
            $errors[] = "Недопустимый язык";
        }
    }

    if (!$contract) {
        $errors[] = "Необходимо согласие с контрактом";
    }

    // 💾 СОХРАНЕНИЕ

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO applications 
            (fullname, phone, email, birthdate, gender, bio, contract_agreed)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $fullname, $phone, $email,
            $birthdate, $gender, $bio, $contract
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
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Форма</h2>

<?php
if (!empty($errors)) {
    foreach ($errors as $e) {
        echo "<div class='error'>$e</div>";
    }
}

if ($success) {
    echo "<div class='success'>Данные успешно сохранены!</div>";
}
?>

<form method="POST">

<input name="fullname" placeholder="ФИО">

<input type="tel" name="phone" placeholder="Телефон">

<input type="email" name="email" placeholder="Email">

<input type="date" name="birthdate">

<label>Пол:</label>
<input type="radio" name="gender" value="male">Мужской
<input type="radio" name="gender" value="female">Женский

<label>ЯП:</label>
<select name="languages[]" multiple>
<?php foreach ($languages as $l): ?>
<option value="<?= $l ?>"><?= $l ?></option>
<?php endforeach; ?>
</select>

<textarea name="bio" placeholder="Биография"></textarea>

<label>
<input type="checkbox" name="contract"> С контрактом ознакомлен
</label>

<button type="submit">Сохранить</button>

</form>

</div>
</body>
</html>