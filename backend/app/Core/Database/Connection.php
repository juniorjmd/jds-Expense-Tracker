<?php
declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOException;

final class Connection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = trim((string) ($_ENV['DB_PORT'] ?? '3306'));
            $db   = $_ENV['DB_DATABASE'] ?? '';
            $user = $_ENV['DB_USERNAME'] ?? '';
            $pass = $_ENV['DB_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            $timeout = (int) ($_ENV['DB_TIMEOUT'] ?? 10);
            $sslMode = strtolower((string) ($_ENV['DB_SSL_MODE'] ?? 'preferred'));
            $sslCa = $_ENV['DB_SSL_CA'] ?? '';
            $useDefaultPort = $port === '' || strtolower($port) === 'default';

            $dsn = "mysql:host={$host};";
            if (!$useDefaultPort) {
                $dsn .= "port={$port};";
            }
            $dsn .= "dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => $timeout,
            ];

            if (\defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset}";
            }

            if (\defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $sslMode === 'required';
            }

            if ($sslCa !== '' && \defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            }

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException(
                    sprintf(
                        '%s [host=%s port=%s ssl_mode=%s timeout=%d]',
                        $e->getMessage(),
                        $host,
                        $useDefaultPort ? 'default' : $port,
                        $sslMode,
                        $timeout
                    ),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }

    private function __construct()
    {
    }
}
