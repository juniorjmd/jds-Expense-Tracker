<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Vehiculos\Repositories\VehiculosRepository;
use App\Modules\Vehiculos\Services\VehiculosService;

class VehiculosServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success
            ? ['success' => true, 'compact_user' => ['id' => 5]]
            : ['success' => false];
    }
}

class VehiculosServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== VEHICULOS SERVICE TEST ==========\n\n";

        $this->testCreateDocumentForVehicleServiceUsesExistingDocument();
        $this->testMissingRequiredFieldFails();
        $this->testAuthFailureFails();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testCreateDocumentForVehicleServiceUsesExistingDocument(): void
    {
        echo "TEST 1: createDocumentForVehicleService returns standard success payload... ";

        try {
            $repository = new class extends VehiculosRepository {
                public array $calls = [];

                public function __construct()
                {
                }

                public function beginTransaction(): bool
                {
                    $this->calls[] = 'begin';
                    return true;
                }

                public function commitTransaction(): bool
                {
                    $this->calls[] = 'commit';
                    return true;
                }

                public function rollBackTransaction(): bool
                {
                    $this->calls[] = 'rollback';
                    return true;
                }

                public function getBoxById(int $boxId): ?array
                {
                    return ['id' => $boxId, 'idBodegaStock' => 7];
                }

                public function getServiceById(int $serviceId): ?array
                {
                    return ['id' => $serviceId, 'nombre' => 'Lavado ejecutivo', 'id_externo' => 'SERV-ODOO'];
                }

                public function getProductExistenceId(int $warehouseId, string $productId): ?int
                {
                    return 33;
                }

                public function insertVehicleIngress(array $payload): bool
                {
                    $this->calls[] = ['ingress', $payload];
                    return true;
                }

                public function insertDocumentProduct(array $payload): bool
                {
                    $this->calls[] = ['document_product', $payload];
                    return true;
                }
            };

            $service = new VehiculosService(
                new Request('POST', [], [], [], []),
                new VehiculosServiceAuthContextStub(true),
                $repository
            );

            $result = $service->createDocumentForVehicleService([
                'placaVehiculo' => 'ABC123',
                'cod_servicio' => 8,
                'propietario' => 21,
                'cod_tipo_vehiculo' => 3,
                'lavador' => 15,
                'cajaAsignada' => 2,
                'valor' => 25000,
                'idDocumento' => 999,
            ]);

            if (($result['idDocumento'] ?? 0) !== 999 || ($result['message'] ?? '') !== 'Servicio vehicular ingresado correctamente') {
                throw new \Exception('Unexpected standard success payload');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testMissingRequiredFieldFails(): void
    {
        echo "TEST 2: missing required field is rejected... ";

        try {
            $repository = new class extends VehiculosRepository {
                public function __construct()
                {
                }

                public function beginTransaction(): bool
                {
                    return true;
                }
            };

            $service = new VehiculosService(
                new Request('POST', [], [], [], []),
                new VehiculosServiceAuthContextStub(true),
                $repository
            );

            $service->createDocumentForVehicleService([
                'placaVehiculo' => 'ABC123',
            ]);

            throw new \Exception('Expected exception was not thrown');
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Error de datos, faltan uno o mas valores para la consulta') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testAuthFailureFails(): void
    {
        echo "TEST 3: authentication is required... ";

        try {
            $repository = new class extends VehiculosRepository {
                public function __construct()
                {
                }
            };

            $service = new VehiculosService(
                new Request('POST', [], [], [], []),
                new VehiculosServiceAuthContextStub(false),
                $repository
            );

            $service->createDocumentForVehicleService([
                'placaVehiculo' => 'ABC123',
                'cod_servicio' => 8,
                'propietario' => 21,
                'cod_tipo_vehiculo' => 3,
                'lavador' => 15,
                'cajaAsignada' => 2,
                'valor' => 25000,
            ]);

            throw new \Exception('Expected exception was not thrown');
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Usuario no autenticado') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function printSummary(): void
    {
        $total = $this->passCount + $this->failCount;
        echo "\n========== RESULT ==========\n";
        echo "PASSED: {$this->passCount}/{$total}\n";
        echo "FAILED: {$this->failCount}/{$total}\n";
        echo "===========================\n\n";
    }
}

(new VehiculosServiceTest())->run();
