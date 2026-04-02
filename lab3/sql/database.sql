-- Создание базы данных
CREATE DATABASE IF NOT EXISTS form_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE form_db;

-- Таблица пользователей (основная)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    biography TEXT,
    agreed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица языков программирования (справочная)
CREATE TABLE IF NOT EXISTS programming_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка допустимых языков
INSERT INTO programming_languages (name) VALUES
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'),
('Python'), ('Java'), ('Haskel'), ('Clojure'), ('Prolog'), ('Scala'), ('Go')
ON DUPLICATE KEY UPDATE name=name;

-- Таблица связи пользователей с языками (один ко многим)
CREATE TABLE IF NOT EXISTS user_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES programming_languages(id),
    UNIQUE KEY unique_user_language (user_id, language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;