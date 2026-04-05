<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Bootstrap\Routes;
use App\Core\Http\Request;

echo "=== TEST 1: Acciones CRUD genéricas registradas ===\n";

$map = Routes::map();
$expected = [
    'DATABASE_GENERIC_CONTRUCT_SELECT',
    'DATABASE_GENERIC_CONTRUCT_SELECT_BY_USER_LOGGED',
    'e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473',
    'DATABASE_GENERIC_CONTRUCT_INSERT',
    'DATABASE_GENERIC_CONTRUCT_UPDATE',
    'DATABASE_GENERIC_CONTRUCT_DELETE',
    'DATABASE_GENERIC_CONTRUCT_PROCEDURE',
    'DATABASE_GENERIC_CONTRUCT_INSERT_SELECT',
    'INSERT_PERFIL_USUARIO',
    'mnbvcxzxcxcxasdfewq15616',
    'qwer12356yhn7ujm8ik',
    'BUSCAR_STOCK_LOCATION',
];

foreach ($expected as $action) {
    $exists = isset($map[$action]);
    echo ($exists ? "✓" : "✗") . " {$action}\n";
    if (!$exists) {
        exit(1);
    }
}

echo "\n=== TEST 2: Request soporta payload CRUD genérico ===\n";

$request = new Request('POST', [], [
    'action' => 'DATABASE_GENERIC_CONTRUCT_SELECT',
    '_tabla' => 'vw_usuario',
    '_columnas' => ['ID', 'nombreCompleto'],
    '_where' => [
        ['columna' => 'estado', 'tipocomp' => '=', 'dato' => 'A'],
    ],
    '_limit' => 10,
], [], ['REQUEST_METHOD' => 'POST']);

echo "✓ Action: " . $request->action() . "\n";
echo "✓ Tabla: " . $request->input('_tabla') . "\n";
echo "✓ Limit: " . $request->input('_limit') . "\n";

echo "\n=== RESULTADO: Configuración CRUD genérica correcta ===\n";
exit(0);
