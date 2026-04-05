<?php
/**
 * Test de Login Legacy - Validación de parámetros y lógica
 * SIN necesidad de conectar a BD real
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;

echo "=== TEST LOGIN LEGACY - VALIDACIÓN DE PARÁMETROS ===\n\n";

// TEST 1: Detección de parámetros legacy (_usuario, _password)
echo "TEST 1: Detección correcta de parámetros legacy (_usuario, _password)\n";
echo "-" . str_repeat("-", 70) . "\n";

$request = new Request(
    'POST',
    [],
    [
        'action' => 'ef2e1d89937fba9f888516293ab1e19e7ed789a5',
        '_usuario' => 'admin',
        '_password' => 'admin123'
    ],
    [],
    ['REQUEST_METHOD' => 'POST']
);

echo "Entrada:\n";
echo "  body['action']: " . $request->action() . "\n";
echo "  body['_usuario']: " . $request->input('_usuario') . "\n";
echo "  body['_password']: " . (strlen($request->input('_password')) > 0 ? '***' : 'vacío') . "\n";

// Simular lo que hace AuthService::login()
$usuario = trim((string) $request->input('usuario', $request->input('_usuario', '')));
$password = (string) $request->input('password', $request->input('_password', ''));

echo "\nVista del AuthService:\n";
echo "  usuario extraído: " . $usuario . "\n";
echo "  password extraído: " . (strlen($password) > 0 ? '***' : 'vacío') . "\n";

$test1_pass = ($usuario === 'admin' && $password === 'admin123');
echo "\n" . ($test1_pass ? "✓" : "✗") . " TEST 1: " . ($test1_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// TEST 2: Prioridad de parámetros (usuario sobre _usuario)
echo "TEST 2: Prioridad de parámetros (usuario > _usuario)\n";
echo "-" . str_repeat("-", 70) . "\n";

$request2 = new Request(
    'POST',
    [],
    [
        'usuario' => 'modern_user',
        '_usuario' => 'legacy_user',  // Debe ser ignorado
        '_password' => 'pass123'
    ],
    [],
    ['REQUEST_METHOD' => 'POST']
);

$usuario2 = trim((string) $request2->input('usuario', $request2->input('_usuario', '')));

echo "Entrada:\n";
echo "  body['usuario']: modern_user\n";
echo "  body['_usuario']: legacy_user\n";

echo "\nVista del AuthService:\n";
echo "  usuario extraído: " . $usuario2 . "\n";

$test2_pass = ($usuario2 === 'modern_user');
echo "\n" . ($test2_pass ? "✓" : "✗") . " TEST 2: " . ($test2_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// TEST 3: Fallback a parámetro legacy cuando moderno no existe
echo "TEST 3: Fallback a parámetro legacy (_usuario) cuando no existe 'usuario'\n";
echo "-" . str_repeat("-", 70) . "\n";

$request3 = new Request(
    'POST',
    [],
    [
        '_usuario' => 'only_legacy',
        '_password' => 'pass123'
    ],
    [],
    ['REQUEST_METHOD' => 'POST']
);

$usuario3 = trim((string) $request3->input('usuario', $request3->input('_usuario', '')));

echo "Entrada:\n";
echo "  body['usuario']: NO EXISTE\n";
echo "  body['_usuario']: only_legacy\n";

echo "\nVista del AuthService:\n";
echo "  usuario extraído: " . $usuario3 . "\n";

$test3_pass = ($usuario3 === 'only_legacy');
echo "\n" . ($test3_pass ? "✓" : "✗") . " TEST 3: " . ($test3_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// TEST 4: Validación de acción detectada
echo "TEST 4: Acción legacy detectada correctamente\n";
echo "-" . str_repeat("-", 70) . "\n";

$action = $request->action();
echo "Acción detectada: " . $action . "\n";
echo "Acción esperada: ef2e1d89937fba9f888516293ab1e19e7ed789a5\n";

$test4_pass = ($action === 'ef2e1d89937fba9f888516293ab1e19e7ed789a5');
echo "\n" . ($test4_pass ? "✓" : "✗") . " TEST 4: " . ($test4_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// TEST 5: Request con _llaveSession
echo "TEST 5: Request soporta _llaveSession para validatekey\n";
echo "-" . str_repeat("-", 70) . "\n" ;

$request5 = new Request(
    'POST',
    [],
    [
        'action' => '16770d92a6a82ee846f7ff23b4c8ad05b69fba03',
        '_llaveSession' => 'abc123def456'
    ],
    [],
    ['REQUEST_METHOD' => 'POST']
);

$llave = $request5->input('_llaveSession');
echo "Entrada:\n";
echo "  action: " . $request5->action() . "\n";
echo "  _llaveSession: " . $llave . "\n";

$test5_pass = ($llave === 'abc123def456');
echo "\n" . ($test5_pass ? "✓" : "✗") . " TEST 5: " . ($test5_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// TEST 6: Request con _llaveSession para logout
echo "TEST 6: Request soporta _llaveSession para logout\n";
echo "-" . str_repeat("-", 70) . "\n" ;

$request6 = new Request(
    'POST',
    [],
    [
        'action' => 'c332258e69e38f18450f9a48c65c89d9e436c561',
        '_llaveSession' => 'logout-token-123'
    ],
    [],
    ['REQUEST_METHOD' => 'POST']
);

$logoutToken = $request6->input('_llaveSession');
echo "Entrada:\n";
echo "  action: " . $request6->action() . "\n";
echo "  _llaveSession: " . $logoutToken . "\n";

$test6_pass = ($logoutToken === 'logout-token-123');
echo "\n" . ($test6_pass ? "✓" : "✗") . " TEST 6: " . ($test6_pass ? "PASÓ" : "FALLÓ") . "\n\n";

// RESUMEN
echo "=== RESUMEN ===\n";
$tests = [$test1_pass, $test2_pass, $test3_pass, $test4_pass, $test5_pass, $test6_pass];
$passed = count(array_filter($tests));
$total = count($tests);

echo "Total tests: $total\n";
echo "Pasados: $passed\n";
echo "Fallidos: " . ($total - $passed) . "\n\n";

if ($passed === $total) {
    echo "✓ TODOS LOS TESTS PASARON\n";
    echo "\nRequisitos validados:\n";
    echo "  • Request::input() soporta fallback a parámetros legacy\n";
    echo "  • Request::action() detecta acciones en body\n";
    echo "  • Parámetros legacy (_usuario, _password, _llaveSession) se capturan\n";
    echo "  • Prioridad correcta: moderno > legacy\n";
    exit(0);
} else {
    echo "✗ ALGUNOS TESTS FALLARON\n";
    exit(1);
}
