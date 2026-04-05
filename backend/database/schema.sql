CREATE DATABASE IF NOT EXISTS jds_expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jds_expense_tracker;

DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    title VARCHAR(120) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactions_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (name, type, color) VALUES
('Salary', 'income', '#10B981'),
('Freelance', 'income', '#3B82F6'),
('Food', 'expense', '#F59E0B'),
('Transport', 'expense', '#8B5CF6');

INSERT INTO transactions (category_id, type, title, amount, transaction_date, notes) VALUES
(1, 'income', 'Monthly salary', 4500.00, CURDATE(), 'Initial seed transaction'),
(3, 'expense', 'Groceries', 120.50, CURDATE(), 'Initial seed transaction');
