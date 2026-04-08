<?php

declare(strict_types=1);

return [
    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? ($_ENV['DB_NAME'] ?? 'jds_expense_tracker'),
    'username' => $_ENV['DB_USERNAME'] ?? ($_ENV['DB_USER'] ?? 'root'),
    'password' => $_ENV['DB_PASSWORD'] ?? ($_ENV['DB_PASS'] ?? ''),
];
