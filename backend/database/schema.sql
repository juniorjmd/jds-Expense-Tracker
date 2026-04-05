SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS user_establishments;
DROP TABLE IF EXISTS expense_templates;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS establishments;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('administrador', 'editor', 'visualizador') NOT NULL DEFAULT 'visualizador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE establishments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_establishments (
    user_id INT UNSIGNED NOT NULL,
    establishment_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, establishment_id),
    CONSTRAINT fk_user_establishments_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_user_establishments_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(20) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expense_templates (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    establishment_id INT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_expense_templates_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    establishment_id INT UNSIGNED NOT NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    from_template TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactions_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Administrador', 'admin@sistema.com', '$2y$10$PyoC.vSuQt9digrMBZ8xzudoR2bdl28IPPJOGh7RGFit08xVQybme', 'administrador');

INSERT INTO establishments (name, description) VALUES
('Casa Matriz', 'Operacion principal del negocio'),
('Sucursal Norte', 'Punto alterno para ventas y gastos');

INSERT INTO user_establishments (user_id, establishment_id) VALUES
(1, 1),
(1, 2);

INSERT INTO categories (name, type, color) VALUES
('Ventas', 'income', '#10B981'),
('Servicios', 'expense', '#F59E0B'),
('Nomina', 'expense', '#EF4444'),
('Arriendo', 'expense', '#6366F1');

INSERT INTO expense_templates (establishment_id, category, description, amount) VALUES
(1, 'Arriendo', 'Pago mensual de arriendo', 900.00);

INSERT INTO transactions (establishment_id, type, category, description, amount, transaction_date, from_template) VALUES
(1, 'income', 'Ventas', 'Ingresos semanales', 2450.00, CURDATE(), 0),
(1, 'expense', 'Nomina', 'Pago de personal', 620.00, CURDATE(), 0),
(2, 'expense', 'Servicios', 'Internet y energia', 180.00, CURDATE(), 0);
