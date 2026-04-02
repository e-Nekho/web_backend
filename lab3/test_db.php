<?php
echo "<h2>Тест подключения к БД</h2>";

// Пробуем подключиться как u82084
try {
    $pdo = new PDO("mysql:host=localhost;dbname=form_db;charset=utf8mb4", 'u82084', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Подключение успешно как u82084!</p>";
    
    // Проверяем таблицы
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    echo "<h3>Существующие таблицы:</h3><ul>";
    foreach ($tables as $table) {
        echo "<li>" . implode('', $table) . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Ошибка: " . $e->getMessage() . "</p>";
    
    // Пробуем создать пользователя
    echo "<h3>Попытка создать пользователя БД:</h3>";
    try {
        $pdo2 = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
        $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo2->exec("CREATE USER IF NOT EXISTS 'u82084'@'localhost'");
        $pdo2->exec("GRANT ALL PRIVILEGES ON form_db.* TO 'u82084'@'localhost'");
        $pdo2->exec("FLUSH PRIVILEGES");
        echo "<p style='color:green'>✓ Пользователь u82084 создан и получил права!</p>";
    } catch (PDOException $e2) {
        echo "<p style='color:red'>✗ Не удалось создать пользователя: " . $e2->getMessage() . "</p>";
    }
}
?>