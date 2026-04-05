<?php
/**
 * tests/Unit/VentasServiceTest.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Ventas\Services\VentasService;

class VentasServiceAuthContextStub
{
    public function __construct(private bool $success = true)
    {
    }

    public function resolve(Request $request): array
    {
        return $this->success
            ? ['success' => true, 'compact_user' => ['id' => 1, 'nombre' => 'Admin']]
            : ['success' => false];
    }
}

class VentasServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== VENTAS SERVICE TEST ==========\n\n";

        $this->testAssignSalesCreditPayments();
        $this->testInvalidDocumentOrderFails();
        $this->testEmptyPaymentsFail();
        $this->testAuthFailureFails();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testAssignSalesCreditPayments(): void
    {
        echo "TEST 1: assignSalesCreditPayments returns legacy document payload... ";

        try {
            $service = new VentasService(
                new Request('POST', [], [], [], []),
                new VentasServiceAuthContextStub(true)
            );

            $result = $service->assignSalesCreditPayments(
                123,
                [
                    ['idMedioDePago' => 1, 'valorPagado' => 10000],
                    ['idMedioDePago' => 2, 'valorPagado' => 25000],
                ],
                2,
                30,
                false
            );

            if ($result['error'] !== 'ok') {
                throw new \Exception('Expected legacy ok response');
            }

            if (($result['numdata'] ?? 0) !== 1) {
                throw new \Exception('Expected numdata=1');
            }

            $document = $result['data']['documentoFinal'] ?? null;
            if (!is_array($document)) {
                throw new \Exception('Expected documentoFinal payload');
            }

            if (($document['orden'] ?? 0) !== 123 || ($document['valorTotalPagado'] ?? 0.0) !== 35000.0) {
                throw new \Exception('Unexpected service response');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testInvalidDocumentOrderFails(): void
    {
        echo "TEST 2: invalid document order is rejected... ";

        try {
            $service = new VentasService(
                new Request('POST', [], [], [], []),
                new VentasServiceAuthContextStub(true)
            );

            $service->assignPurchaseCreditPayments(0, [['valorPagado' => 1000]]);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Orden de documento inválida') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testEmptyPaymentsFail(): void
    {
        echo "TEST 3: empty payments are rejected... ";

        try {
            $service = new VentasService(
                new Request('POST', [], [], [], []),
                new VentasServiceAuthContextStub(true)
            );

            $service->assignCreditInstallmentPayment(123, []);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'Debe enviar al menos un pago') {
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
        echo "TEST 4: authentication is required... ";

        try {
            $service = new VentasService(
                new Request('POST', [], [], [], []),
                new VentasServiceAuthContextStub(false)
            );

            $service->assignSalesCreditPayments(123, [['valorPagado' => 1000]]);
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

(new VentasServiceTest())->run();
