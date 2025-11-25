CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(25) NOT NULL,
    surname VARCHAR(25) NOT NULL,
    eMail VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    secret_question VARCHAR(255) NULL,
    secret VARCHAR(255) NULL,
    failed_attempts INT NULL,
    last_failed DATETIME NULL,
    locked_until DATETIME NULL
);