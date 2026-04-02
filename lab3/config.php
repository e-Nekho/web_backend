<?php
// config.php - отдельный файл с настройками
define('DB_HOST', 'localhost');
define('DB_NAME', 'u68592');
define('DB_USER', 'u68592');
define('DB_PASS', '6714103');

// Допустимые значения для валидации
define('ALLOWED_GENDERS', serialize(['male', 'female', 'other']));
define('ALLOWED_LANGUAGES', serialize([
    'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
    'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'
]));

function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB Connection Error: " . $e->getMessage());
        throw new Exception("Ошибка подключения к базе данных. Пожалуйста, обратитесь к администратору.");
    }
}
?>