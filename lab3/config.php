<?php
// config.php - отдельный файл с настройками
define('DB_HOST', 'localhost');
define('DB_NAME', 'form_db');
define('DB_USER', 'root');
define('DB_PASS', '');

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
        die("Connection failed: " . $e->getMessage());
    }
}
?>