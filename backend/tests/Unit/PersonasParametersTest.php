<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class PersonasParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== PERSONAS PARAMETERS TEST ==========\n\n";

        $this->testPersonasActionsMapping();
        $this->testRoutesLoadsPersonasActions();
        $this->testPersonasControllerExists();
        $this->testActionDetected();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testPersonasActionsMapping(): void
    {
        echo "TEST 1: Personas actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/personas-actions.php';

            foreach (['BUSCAR_ODOO_TITULO_PERSONA', 'GET_MAESTROS_CLIENTES'] as $action) {
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

    private function testRoutesLoadsPersonasActions(): void
    {
        echo "TEST 2: Routes loads personas actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            foreach (['BUSCAR_ODOO_TITULO_PERSONA', 'GET_MAESTROS_CLIENTES'] as $action) {
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

    private function testPersonasControllerExists(): void
    {
        echo "TEST 3: PersonasController has required methods... ";

        try {
            $controller = \App\Modules\Personas\PersonasController::class;

            foreach (['searchOdooPersonTitle', 'getClientMasters'] as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found");
                }
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
        echo "TEST 4: Action is detected correctly... ";

        try {
            $request = new \App\Core\Http\Request('POST', [], ['action' => 'GET_MAESTROS_CLIENTES'], [], []);

            if ($request->action() !== 'GET_MAESTROS_CLIENTES') {
                throw new \Exception('action mismatch');
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

(new PersonasParametersTest())->run();
