<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = trim((string) ($_ENV['DB_PORT'] ?? '3306'));
$db = $_ENV['DB_DATABASE'] ?? '';
$user = $_ENV['DB_USERNAME'] ?? '';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
$timeout = (int) ($_ENV['DB_TIMEOUT'] ?? 10);
$sslMode = strtolower((string) ($_ENV['DB_SSL_MODE'] ?? 'preferred'));
$sslCa = $_ENV['DB_SSL_CA'] ?? '';
$useDefaultPort = $port === '' || strtolower($port) === 'default';

$dsn = sprintf('mysql:host=%s;', $host);
if (!$useDefaultPort) {
    $dsn .= sprintf('port=%s;', $port);
}
$dsn .= sprintf('dbname=%s;charset=%s', $db, $charset);
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

$pdo = new PDO($dsn, $user, $pass, $options);

$sql = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
if ($sql === false) {
    throw new RuntimeException('No se pudo leer el archivo schema.sql');
}

$pdo->exec($sql);

echo "schema-applied\n";
