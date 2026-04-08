<?php

declare(strict_types=1);

namespace App\Persistence;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require dirname(__DIR__) . '/Config/database.php';
        $port = trim((string) ($config['port'] ?? '3306'));
        $useDefaultPort = $port === '' || strtolower($port) === 'default';

        $dsn = sprintf('%s:host=%s;', $config['driver'], $config['host']);
        if (!$useDefaultPort) {
            $dsn .= sprintf('port=%s;', $port);
        }
        $dsn .= sprintf('dbname=%s;charset=utf8mb4', $config['database']);

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection error: ' . $e->getMessage());
        }

        return self::$connection;
    }
}
