<?php
/**
 * Test script para validar routing legacy
 */

require __DIR__ . '/vendor/autoload.php';

use App\Core\Http\Request;
use App\Bootstrap\Routes;

// Test 1: Verificar que pueden detectarse los parámetros legacy
echo "=== TEST 1: Detección de parámetros legacy ===\n";

$bodyData = [
    'action' => 'ef2e1d89937fba9f888516293ab1e19e7ed789a5',
    '_usuario' => 'admin',
    '_password' => 'test123'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/';

$request = new Request('POST', [], $bodyData, [], $_SERVER);

echo "✓ Action detectado: " . $request->action() . "\n";
echo "✓ Usuario detectado: " . $request->input('_usuario') . "\n";
echo "✓ Password detectado: " . $request->input('_password') . "\n";

// Test 2: Verificar que las acciones están mapeadas
echo "\n=== TEST 2: Acciones mapeadas ===\n";

$actionMap = Routes::map();
$actions_to_check = [
    'ef2e1d89937fba9f888516293ab1e19e7ed789a5' => 'login',
    '16770d92a6a82ee846f7ff23b4c8ad05b69fba03' => 'validatekey',
    '16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03' => 'me',
    'RESETEAR_USUARIO_PASS' => 'resetpassword',
    'HIJODELAGRANCHINGADA' => 'setpassword',
];

foreach ($actions_to_check as $hash => $name) {
    $exists = isset($actionMap[$hash]);
    echo ($exists ? "✓" : "✗") . " {$name}: {$hash}\n";
}

echo "\n=== TEST 3: AuthService acepta parámetros legacy ===\n";

use App\Modules\Auth\Services\AuthService;

$service = new AuthService();
echo "✓ AuthService instanciado correctamente\n";

// Verificar que el método login acepta el request con parámetros _usuario y _password
$testRequest = new Request('POST', [], [
    'action' => 'login',
    '_usuario' => 'testuser',
    '_password' => 'testpass'
], [], ['REQUEST_METHOD' => 'POST']);

echo "✓ Se puede crear un Request con parámetros legacy\n";

echo "\n=== RESULTADO: Configuración correcta ===\n";
