<?php
/**
 * tests/Unit/CarwashParametersTest.php
 * 
 * Tests que validan:
 * 1. Mapeo de acciones legacy de Carwash
 * 2. Carga correcta en Routes.php
 * 3. Request acepta parámetros legacy
 * 4. Acción se detecta correctamente
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class CarwashParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run()
    {
        echo "\n========== CARWASH PARAMETERS TEST ==========\n\n";

        $this->testCarwashActionsMapping();
        $this->testRoutesLoadsCarwashActions();
        $this->testCarwashControllerExists();
        $this->testOpenBoxAcceptsLegacyParameters();
        $this->testActionDetected();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testCarwashActionsMapping(): void
    {
        echo "TEST 1: Carwash actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/carwash-actions.php';

            $expectedActions = [
                'ABRIR_CAJA_ACTIVA',
                'CERRAR_CAJA_ACTIVA',
                'CERRAR_CAJA_PARCIAL',
                'OBTENER_RESUMEN_CAJA',
            ];

            foreach ($expectedActions as $action) {
                if (!array_key_exists($action, $actions)) {
                    throw new \Exception("Missing action: $action");
                }
                if (!is_array($actions[$action])) {
                    throw new \Exception("Action $action is not an array");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsCarwashActions(): void
    {
        echo "TEST 2: Routes loads carwash actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            $expectedActions = [
                'ABRIR_CAJA_ACTIVA',
                'CERRAR_CAJA_ACTIVA',
                'CERRAR_CAJA_PARCIAL',
                'OBTENER_RESUMEN_CAJA',
            ];

            foreach ($expectedActions as $action) {
                if (!array_key_exists($action, $map)) {
                    throw new \Exception("Action $action not found in Routes::map()");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCarwashControllerExists(): void
    {
        echo "TEST 3: CarwashController has required methods... ";

        try {
            $controller = \App\Modules\Carwash\CarwashController::class;

            $methods = ['openBox', 'closeBox', 'closePartialBox', 'getBoxSummary'];

            foreach ($methods as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found in CarwashController");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testOpenBoxAcceptsLegacyParameters(): void
    {
        echo "TEST 4: openBox accepts legacy parameters... ";

        try {
            // Crear request con parámetros legacy usando constructor directo
            $body = [
                'action' => 'ABRIR_CAJA_ACTIVA',
                '_usuario' => 'admin',
                '_password' => 'admin123',
                '_llaveSession' => 'token123',
                'caja_motivo' => 'Apertura de jornada',
                'caja_monto_inicial' => 500000
            ];

            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            // Verificar que el request puede extraer los parámetros
            $motivo = $request->input('caja_motivo', '');
            $monto = $request->input('caja_monto_inicial', 0);

            if ($motivo !== 'Apertura de jornada') {
                throw new \Exception("Failed to extract caja_motivo: got '$motivo'");
            }

            if ($monto !== 500000) {
                throw new \Exception("Failed to extract caja_monto_inicial: got '$monto'");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionDetected(): void
    {
        echo "TEST 5: Action is detected correctly... ";

        try {
            $body = ['action' => 'ABRIR_CAJA_ACTIVA'];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);
            $action = $request->action();

            if ($action !== 'ABRIR_CAJA_ACTIVA') {
                throw new \Exception("Failed to detect action. Got: '$action'");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
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

$test = new CarwashParametersTest();
$test->run();
