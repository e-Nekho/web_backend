CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(64) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fullname VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birthdate DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    bio TEXT NOT NULL,
    contract_agreed BOOLEAN NOT NULL
);

CREATE TABLE programming_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE
);

INSERT INTO programming_languages (name) VALUES
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'),
('Python'), ('Java'), ('Haskel'), ('Clojure'),
('Prolog'), ('Scala'), ('Go');

CREATE TABLE application_languages (
    application_id INT,
    language_id INT,
    FOREIGN KEY (application_id) REFERENCES applications(id),
    FOREIGN KEY (language_id) REFERENCES programming_languages(id)
);