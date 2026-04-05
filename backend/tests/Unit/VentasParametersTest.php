<?php
/**
 * tests/Unit/VentasParametersTest.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class VentasParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== VENTAS PARAMETERS TEST ==========\n\n";

        $this->testVentasActionsMapping();
        $this->testRoutesLoadsVentasActions();
        $this->testVentasControllerExists();
        $this->testLegacyParametersFallback();
        $this->testActionDetected();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testVentasActionsMapping(): void
    {
        echo "TEST 1: Ventas actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/ventas-actions.php';
            $expectedActions = [
                'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO',
                'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION',
                'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO',
                'ASIGNAR_ABONO_DOCUMENTOS_CREDITO',
            ];

            foreach ($expectedActions as $action) {
                if (!array_key_exists($action, $actions)) {
                    throw new \Exception("Missing action: $action");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsVentasActions(): void
    {
        echo "TEST 2: Routes loads ventas actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();
            $expectedActions = [
                'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO',
                'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION',
                'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO',
                'ASIGNAR_ABONO_DOCUMENTOS_CREDITO',
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

    private function testVentasControllerExists(): void
    {
        echo "TEST 3: VentasController has required methods... ";

        try {
            $controller = \App\Modules\Ventas\VentasController::class;
            $methods = [
                'assignPurchaseCreditPayments',
                'updatePurchaseCreditPayments',
                'assignSalesCreditPayments',
                'assignCreditInstallmentPayment',
            ];

            foreach ($methods as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found in VentasController");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyParametersFallback(): void
    {
        echo "TEST 4: Ventas actions accept legacy parameters... ";

        try {
            $body = [
                'action' => 'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO',
                '_ordenDocumento' => 123,
                '_numCuotas' => 2,
                '_numDiasCuotas' => 30,
                '_pagos' => [
                    ['idMedioDePago' => 1, 'valorPagado' => 50000],
                ],
            ];

            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            if ($request->input('ordenDocumento', 0) !== 123) {
                throw new \Exception('Failed to extract _ordenDocumento');
            }

            if ($request->input('numCuotas', 0) !== 2) {
                throw new \Exception('Failed to extract _numCuotas');
            }

            if ($request->input('numDiasCuotas', 0) !== 30) {
                throw new \Exception('Failed to extract _numDiasCuotas');
            }

            $payments = $request->input('pagos', []);
            if (!is_array($payments) || count($payments) !== 1) {
                throw new \Exception('Failed to extract _pagos');
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
            $request = new \App\Core\Http\Request(
                'POST',
                [],
                ['action' => 'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO'],
                [],
                []
            );

            if ($request->action() !== 'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO') {
                throw new \Exception('Failed to detect legacy action');
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

(new VentasParametersTest())->run();
