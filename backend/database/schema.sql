SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS company_access_logs;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS user_establishments;
DROP TABLE IF EXISTS expense_templates;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS establishments;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE companies (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id INT UNSIGNED NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superusuario', 'administrador', 'editor', 'visualizador') NOT NULL DEFAULT 'visualizador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_users_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE establishments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_establishments_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
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
    company_id INT UNSIGNED NOT NULL,
    establishment_id INT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_expense_templates_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_expense_templates_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    establishment_id INT UNSIGNED NOT NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    from_template TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transactions_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_transactions_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE company_access_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NOT NULL,
    action VARCHAR(80) NOT NULL DEFAULT 'view_company_overview',
    note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_company_access_logs_company_created (company_id, created_at),
    KEY idx_company_access_logs_actor_created (actor_user_id, created_at),
    CONSTRAINT fk_company_access_logs_actor
        FOREIGN KEY (actor_user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_company_access_logs_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO companies (name, description) VALUES
('Demo Company', 'Empresa semilla para pruebas iniciales');

INSERT INTO users (company_id, full_name, email, password_hash, role) VALUES
(NULL, 'Super Usuario', 'admin@sistema.com', '$2y$10$PyoC.vSuQt9digrMBZ8xzudoR2bdl28IPPJOGh7RGFit08xVQybme', 'superusuario'),
(1, 'Administrador Demo', 'admin.demo@sistema.com', '$2y$10$PyoC.vSuQt9digrMBZ8xzudoR2bdl28IPPJOGh7RGFit08xVQybme', 'administrador');

INSERT INTO establishments (company_id, name, description) VALUES
(1, 'Casa Matriz', 'Operacion principal del negocio'),
(1, 'Sucursal Norte', 'Punto alterno para ventas y gastos');

INSERT INTO user_establishments (user_id, establishment_id) VALUES
(2, 1),
(2, 2);

INSERT INTO categories (name, type, color) VALUES
('Ventas', 'income', '#10B981'),
('Servicios', 'expense', '#F59E0B'),
('Nomina', 'expense', '#EF4444'),
('Arriendo', 'expense', '#6366F1');

INSERT INTO expense_templates (company_id, establishment_id, category, description, amount) VALUES
(1, 1, 'Arriendo', 'Pago mensual de arriendo', 900.00);

INSERT INTO transactions (company_id, establishment_id, type, category, description, amount, transaction_date, from_template) VALUES
(1, 1, 'income', 'Ventas', 'Ingresos semanales', 2450.00, CURDATE(), 0),
(1, 1, 'expense', 'Nomina', 'Pago de personal', 620.00, CURDATE(), 0),
(1, 2, 'expense', 'Servicios', 'Internet y energia', 180.00, CURDATE(), 0);
