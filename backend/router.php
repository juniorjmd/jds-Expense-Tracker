<?php
/**
 * Router script para PHP built-in server
 * Todos los archivos no-físicos deben pasar por public/index.php
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = __DIR__ . '/public';

// Si es un archivo físico que existe, servarlo
if (file_exists($publicDir . $requestUri)) {
    return false;
}

// Si no existe, pasar a public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicDir . '/index.php';

require $publicDir . '/index.php';
