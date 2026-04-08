SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS company_access_logs;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS company_subscriptions;
DROP TABLE IF EXISTS plans;
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

CREATE TABLE plans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    monthly_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    establishments_limit INT UNSIGNED NULL,
    users_limit INT UNSIGNED NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
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

CREATE TABLE company_subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('trial', 'active', 'past_due', 'suspended', 'cancelled') NOT NULL DEFAULT 'trial',
    starts_at DATE NOT NULL,
    ends_at DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_company_subscriptions_company_status (company_id, status, starts_at),
    CONSTRAINT fk_company_subscriptions_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_company_subscriptions_plan
        FOREIGN KEY (plan_id) REFERENCES plans(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE company_settings (
    company_id INT UNSIGNED NOT NULL,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'COP',
    timezone VARCHAR(60) NOT NULL DEFAULT 'America/Bogota',
    date_format VARCHAR(30) NOT NULL DEFAULT 'Y-m-d',
    branding_name VARCHAR(120) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (company_id),
    CONSTRAINT fk_company_settings_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
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

CREATE TABLE activity_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_user_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED NULL,
    establishment_id INT UNSIGNED NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id VARCHAR(60) NOT NULL,
    action VARCHAR(80) NOT NULL,
    note VARCHAR(255) NULL,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_logs_company_created (company_id, created_at),
    KEY idx_activity_logs_actor_created (actor_user_id, created_at),
    CONSTRAINT fk_activity_logs_actor
        FOREIGN KEY (actor_user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_activity_logs_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_activity_logs_establishment
        FOREIGN KEY (establishment_id) REFERENCES establishments(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO plans (code, name, description, monthly_price, establishments_limit, users_limit, is_default, is_active) VALUES
('free', 'Plan Free', 'Base inicial para una empresa pequena en fase de arranque.', 0.00, 2, 5, 1, 1),
('growth', 'Plan Growth', 'Escala operaciones con mas usuarios y establecimientos.', 79.00, 10, 50, 0, 1);

INSERT INTO users (company_id, full_name, email, password_hash, role) VALUES
(NULL, 'Junior Dominguez', 'juniorjm@gmail.com', '$2y$10$1.xGr53flOgTrb3DuoGFfOmgUg2ujvH.Qd.vrs17e90Twu09LTXRK', 'superusuario');
