<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class VehiculosParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== VEHICULOS PARAMETERS TEST ==========\n\n";

        $this->testVehiculosActionsMapping();
        $this->testRoutesLoadsVehiculosActions();
        $this->testVehiculosControllerExists();
        $this->testLegacyArraydatosFallback();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testVehiculosActionsMapping(): void
    {
        echo "TEST 1: Vehiculos actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/vehiculos-actions.php';

            if (!array_key_exists('CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO', $actions)) {
                throw new \Exception('Missing action CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsVehiculosActions(): void
    {
        echo "TEST 2: Routes loads vehiculos actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            if (!array_key_exists('CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO', $map)) {
                throw new \Exception('Action CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO not found in Routes::map()');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testVehiculosControllerExists(): void
    {
        echo "TEST 3: VehiculosController has required method... ";

        try {
            $controller = \App\Modules\Vehiculos\VehiculosController::class;

            if (!method_exists($controller, 'createDocumentForVehicleService')) {
                throw new \Exception('Method createDocumentForVehicleService not found');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyArraydatosFallback(): void
    {
        echo "TEST 4: Vehiculos action accepts legacy _arraydatos... ";

        try {
            $payload = [
                'action' => 'CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO',
                '_arraydatos' => [
                    'placaVehiculo' => 'ABC123',
                    'cod_servicio' => 8,
                ],
            ];

            $request = new \App\Core\Http\Request('POST', [], $payload, [], []);
            $arraydatos = $request->input('arraydatos', []);

            if (!is_array($arraydatos) || ($arraydatos['placaVehiculo'] ?? '') !== 'ABC123') {
                throw new \Exception('Failed to extract _arraydatos');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
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

(new VehiculosParametersTest())->run();
