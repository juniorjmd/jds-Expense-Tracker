<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Repositories\UserRepository;
use App\Services\CompanyService;
use App\Services\EstablishmentService;
use App\Services\TransactionService;
use App\Services\UserService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

echo "=== TEST EXPENSE TRACKER SAAS ===\n\n";

$userRepository = new UserRepository();
$companyService = new CompanyService();
$establishmentService = new EstablishmentService();
$transactionService = new TransactionService();
$userService = new UserService();

$super = $userRepository->findByEmail('admin@sistema.com');
$adminDemo = $userRepository->findByEmail('admin.demo@sistema.com');

if ($super === null || $adminDemo === null) {
    echo "✗ No fue posible cargar usuarios semilla.\n";
    exit(1);
}

$tests = [];

echo "TEST 1: El superusuario no puede ver resumen global sin seleccionar empresa\n";
echo str_repeat('-', 72) . "\n";
try {
    $transactionService->summary($super, date('Y-m'), null);
    $tests[] = false;
    echo "✗ Se permitió un resumen global no autorizado.\n\n";
} catch (InvalidArgumentException $exception) {
    $tests[] = str_contains($exception->getMessage(), 'empresa especifica');
    echo "✓ Bloqueo correcto: {$exception->getMessage()}\n\n";
}

echo "TEST 2: Crear empresa genera base SaaS inicial\n";
echo str_repeat('-', 72) . "\n";
$suffix = (string) time();
$created = $companyService->create($super, [
    'name' => "Empresa Test {$suffix}",
    'description' => 'Empresa creada desde test de integracion.',
    'adminName' => 'Admin Test',
    'adminEmail' => "admin.test.{$suffix}@sistema.com",
    'adminPassword' => 'admin123',
]);

$companyId = (int) ($created['company']['id'] ?? 0);
$adminUserId = (int) ($created['adminUser']['id'] ?? 0);
$companyCreatedOk = $companyId > 0
    && ($created['company']['planCode'] ?? '') === 'free'
    && ($created['company']['subscriptionStatus'] ?? '') === 'active'
    && ($created['company']['currencyCode'] ?? '') === 'COP';
$tests[] = $companyCreatedOk;
echo ($companyCreatedOk ? "✓" : "✗") . " Empresa creada con plan/configuracion por defecto.\n\n";

echo "TEST 3: Entrar al detalle crea trazabilidad de acceso y actividad\n";
echo str_repeat('-', 72) . "\n";
$newEstablishment = $establishmentService->create($super, [
    'companyId' => $companyId,
    'name' => 'Sucursal Test',
    'description' => 'Creada para validar auditoria y operaciones.',
]);

$transactionService->create($super, (int) $newEstablishment['id'], [
    'type' => 'income',
    'category' => 'Ventas',
    'description' => 'Ingreso inicial de prueba',
    'amount' => 123.45,
    'transaction_date' => date('Y-m-d'),
]);

$overview = $companyService->overview($super, $companyId, date('Y-m'));
$hasAccessLog = !empty($overview['accessLogs']) && ($overview['accessLogs'][0]['action'] ?? '') === 'view_company_overview';
$hasActivityLog = false;
foreach ($overview['activityLogs'] as $log) {
    if (($log['action'] ?? '') === 'transaction_created') {
        $hasActivityLog = true;
        break;
    }
}
$tests[] = $hasAccessLog && $hasActivityLog;
echo (($hasAccessLog && $hasActivityLog) ? "✓" : "✗") . " El detalle registra acceso y actividad critica.\n\n";

echo "TEST 4: Un administrador normal solo ve usuarios de su empresa\n";
echo str_repeat('-', 72) . "\n";
$visibleUsers = $userService->list($adminDemo);
$adminScopeOk = !empty($visibleUsers);
foreach ($visibleUsers as $visibleUser) {
    if (($visibleUser['companyId'] ?? null) !== ($adminDemo['company_id'] !== null ? (string) $adminDemo['company_id'] : null)) {
        $adminScopeOk = false;
        break;
    }
    if (($visibleUser['role'] ?? '') === 'superusuario') {
        $adminScopeOk = false;
        break;
    }
}
$tests[] = $adminScopeOk;
echo ($adminScopeOk ? "✓" : "✗") . " El admin demo solo recibe usuarios de su empresa.\n\n";

echo "TEST 5: No se puede eliminar el ultimo administrador de una empresa\n";
echo str_repeat('-', 72) . "\n";
$newAdminActor = $userRepository->find($adminUserId);
try {
    $userService->delete($super, $adminUserId);
    $tests[] = false;
    echo "✗ Se eliminó al ultimo administrador de la empresa.\n\n";
} catch (InvalidArgumentException $exception) {
    $tests[] = str_contains($exception->getMessage(), 'al menos un administrador');
    echo "✓ Bloqueo correcto: {$exception->getMessage()}\n\n";
}

echo "TEST 6: Un usuario no puede eliminarse a si mismo\n";
echo str_repeat('-', 72) . "\n";
if ($newAdminActor === null) {
    $tests[] = false;
    echo "✗ No fue posible recargar el administrador creado.\n\n";
} else {
    try {
        $userService->delete($newAdminActor, $adminUserId);
        $tests[] = false;
        echo "✗ Se permitió la autoeliminación.\n\n";
    } catch (InvalidArgumentException $exception) {
        $tests[] = str_contains($exception->getMessage(), 'propio usuario');
        echo "✓ Bloqueo correcto: {$exception->getMessage()}\n\n";
    }
}

$passed = count(array_filter($tests));
$total = count($tests);

echo "=== RESUMEN ===\n";
echo "Total tests: {$total}\n";
echo "Pasados: {$passed}\n";
echo "Fallidos: " . ($total - $passed) . "\n\n";

if ($passed === $total) {
    echo "✓ TEST EXPENSE TRACKER SAAS COMPLETADO\n";
    exit(0);
}

echo "✗ TEST EXPENSE TRACKER SAAS FALLÓ\n";
exit(1);
