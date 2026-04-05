<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Personas\Services\PersonasService;

class PersonasServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success ? ['success' => true, 'user_id' => 1] : ['success' => false];
    }
}

class PersonasServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== PERSONAS SERVICE TEST ==========\n\n";

        $this->testSearchOdooPersonTitleReturnsArray();
        $this->testGetClientMastersReturnsArray();
        $this->testServiceRequiresAuthentication();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(bool $success = true): PersonasService
    {
        return new PersonasService(new Request('POST', [], [], [], []), new PersonasServiceAuthContextStub($success));
    }

    private function testSearchOdooPersonTitleReturnsArray(): void
    {
        echo "TEST 1: searchOdooPersonTitle returns data... ";

        try {
            $result = $this->service()->searchOdooPersonTitle();

            if (!is_array($result) || ($result['error'] ?? '') !== 'ok' || !isset($result['data'][0]['display_name'])) {
                throw new \Exception('invalid Odoo title response');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testGetClientMastersReturnsArray(): void
    {
        echo "TEST 2: getClientMasters returns data... ";

        try {
            $result = $this->service()->getClientMasters();

            if (($result['error'] ?? '') !== 'ok' || !isset($result['datos']['parametros'], $result['datos']['tipo_id_clientes'], $result['datos']['ciudades'])) {
                throw new \Exception('invalid client masters response');
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
        echo "TEST 3: Personas service requires authentication... ";

        try {
            $this->service(false)->getClientMasters();
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
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

(new PersonasServiceTest())->run();
