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

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database']
        );

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
