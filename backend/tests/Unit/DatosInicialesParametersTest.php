<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class DatosInicialesParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DATOS INICIALES PARAMETERS TEST ==========\n\n";

        $this->testDatosInicialesActionsMapping();
        $this->testRoutesLoadsDatosInicialesActions();
        $this->testDatosInicialesControllerExists();
        $this->testActionDetected();
        $this->testLegacyDatosInicialesActionsAreMapped();
        $this->testLegacyDatosInicialesParametersAreParsed();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testDatosInicialesActionsMapping(): void
    {
        echo "TEST 1: DatosIniciales actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/datosiniciales-actions.php';

            if (!array_key_exists('GET_SUCURSAL_PRINCIPAL_DATA', $actions)) {
                throw new \Exception('Missing action GET_SUCURSAL_PRINCIPAL_DATA');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyDatosInicialesActionsAreMapped(): void
    {
        echo "TEST 5: DatosIniciales legacy visible actions are mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/datosiniciales-actions.php';
            $expected = [
                '52444d9072f7ec12a26cb2879ebb4ab0bf5aa553',
                '52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA',
                '23929870008e23007350be74a708ab3a806dce13',
                '8e9ae038c37d3b59fc1eed456c77aefb5eadffea',
                '99c505a66a9d8a984059baf1b99bb9e6456ae4bb',
            ];

            foreach ($expected as $action) {
                if (!array_key_exists($action, $actions)) {
                    throw new \Exception("Missing action {$action}");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyDatosInicialesParametersAreParsed(): void
    {
        echo "TEST 6: DatosIniciales legacy parameters are parsed... ";

        try {
            $request = new \App\Core\Http\Request('POST', [], [
                'action' => '99c505a66a9d8a984059baf1b99bb9e6456ae4bb',
                'componente' => '1',
                'formulario' => '12',
                'preguntas' => ['42', '45', '46'],
                '_poikmjuy' => 'old-pass',
                '_wsxedc' => 'new-pass',
                '_kjhgtyuhybv' => 'new-pass',
                '_llaveSession' => 'session-key',
                '_qazxswe' => '25',
                '_r1548juy' => '9',
                '_85247efg' => '77',
            ], [], []);

            if ((int) $request->input('componente', 0) !== 1) {
                throw new \Exception('componente mismatch');
            }

            if ((int) $request->input('formulario', 0) !== 12) {
                throw new \Exception('formulario mismatch');
            }

            if ($request->input('llaveSession', '') !== 'session-key') {
                throw new \Exception('llaveSession mismatch');
            }

            if ((int) $request->input('r1548juy', 0) !== 9) {
                throw new \Exception('r1548juy mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testRoutesLoadsDatosInicialesActions(): void
    {
        echo "TEST 2: Routes loads datosIniciales actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            if (!array_key_exists('GET_SUCURSAL_PRINCIPAL_DATA', $map)) {
                throw new \Exception('Action GET_SUCURSAL_PRINCIPAL_DATA not found in Routes::map()');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testDatosInicialesControllerExists(): void
    {
        echo "TEST 3: DatosInicialesController has required method... ";

        try {
            $controller = \App\Modules\DatosIniciales\DatosInicialesController::class;

            if (!method_exists($controller, 'getPrincipalBranchData')) {
                throw new \Exception('Method getPrincipalBranchData not found');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionDetected(): void
    {
        echo "TEST 4: Action is detected correctly... ";

        try {
            $request = new \App\Core\Http\Request('POST', [], ['action' => 'GET_SUCURSAL_PRINCIPAL_DATA'], [], []);

            if ($request->action() !== 'GET_SUCURSAL_PRINCIPAL_DATA') {
                throw new \Exception('action mismatch');
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

(new DatosInicialesParametersTest())->run();
