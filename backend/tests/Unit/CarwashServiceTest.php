<?php
/**
 * tests/Unit/CarwashServiceTest.php
 * 
 * Tests que validan:
 * 1. CarwashService existe con métodos correctos
 * 2. Respuestas tienen estructura JSON esperada
 * 3. Manejo de errores
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class CarwashServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    /**
     * Crea un mock de AuthContext compatible con resolve()
     */
    private function createMockAuthContext(?array $user): object
    {
        return new class($user) {
            private $userData;

            public function __construct($data)
            {
                $this->userData = $data;
            }

            public function resolve($request): array
            {
                return $this->userData === null
                    ? ['success' => false, 'message' => 'Usuario no autenticado']
                    : [
                        'success' => true,
                        'compact_user' => [
                            'id' => $this->userData['ID'] ?? 1,
                            'nombre' => $this->userData['USUARIO'] ?? 'admin',
                        ],
                    ];
            }
        };
    }

    public function run()
    {
        echo "\n========== CARWASH SERVICE TEST ==========\n\n";

        $this->testCarwashServiceExists();
        $this->testResponseStructureSuccess();
        $this->testResponseStructureError();
        $this->testOpenBoxReturnType();
        $this->testServiceRequiresAuthentication();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testCarwashServiceExists(): void
    {
        echo "TEST 1: CarwashService exists and has methods... ";

        try {
            $service = \App\Modules\Carwash\Services\CarwashService::class;

            $methods = ['openBox', 'closeBox', 'closePartialBox', 'getBoxSummary'];

            foreach ($methods as $method) {
                if (!method_exists($service, $method)) {
                    throw new \Exception("Method $method not found in CarwashService");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testResponseStructureSuccess(): void
    {
        echo "TEST 2: Response has correct JSON structure (success)... ";

        try {
            $body = ['action' => 'ABRIR_CAJA_ACTIVA', 'caja_motivo' => 'Test'];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            // Mock AuthContext usando un proxy
            $mockAuthContext = $this->createMockAuthContext(['USUARIO' => 'test_user']);

            $service = new \App\Modules\Carwash\Services\CarwashService($request, $mockAuthContext);
            $result = $service->openBox('Test motivo', 500000);

            if (!is_array($result)) {
                throw new \Exception("openBox did not return array");
            }

            if (!array_key_exists('message', $result)) {
                throw new \Exception("Response missing message");
            }

            if (!array_key_exists('box', $result)) {
                throw new \Exception("Response missing box");
            }

            if (!array_key_exists('caja_id', $result['box'])) {
                throw new \Exception("Response box missing caja_id");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testResponseStructureError(): void
    {
        echo "TEST 3: Error response has correct structure... ";

        try {
            $body = [];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $mockAuthContext = $this->createMockAuthContext(null); // Sin usuario

            $service = new \App\Modules\Carwash\Services\CarwashService($request, $mockAuthContext);

            try {
                $service->openBox();
                throw new \Exception("Expected exception for unauthenticated user");
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Usuario no autenticado') === false) {
                    throw new \Exception("Wrong error message: {$e->getMessage()}");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testOpenBoxReturnType(): void
    {
        echo "TEST 4: openBox returns correct data types... ";

        try {
            $body = ['action' => 'ABRIR_CAJA_ACTIVA'];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $mockAuthContext = $this->createMockAuthContext(['USUARIO' => 'admin', 'ID' => 99]);

            $service = new \App\Modules\Carwash\Services\CarwashService($request, $mockAuthContext);
            $result = $service->openBox('Test', 500000);

            if (!is_int($result['box']['caja_id'])) {
                throw new \Exception("caja_id must be int");
            }

            if (!is_string($result['box']['usuario'])) {
                throw new \Exception("usuario must be string");
            }

            if (!is_string($result['box']['estado'])) {
                throw new \Exception("estado must be string");
            }

            if (!is_numeric($result['box']['monto_inicial'])) {
                throw new \Exception("monto_inicial must be numeric");
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testServiceRequiresAuthentication(): void
    {
        echo "TEST 5: All methods require authentication... ";

        try {
            $body = [];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $mockAuthContext = $this->createMockAuthContext(null);

            $service = new \App\Modules\Carwash\Services\CarwashService($request, $mockAuthContext);

            $methods = ['openBox', 'closeBox', 'closePartialBox', 'getBoxSummary'];

            foreach ($methods as $method) {
                try {
                    $service->$method();
                    throw new \Exception("$method did not throw exception for unauthenticated user");
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Usuario no autenticado') === false) {
                        throw $e;
                    }
                }
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

$test = new CarwashServiceTest();
$test->run();
