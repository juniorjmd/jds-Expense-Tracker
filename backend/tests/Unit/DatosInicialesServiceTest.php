<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Modules\DatosIniciales\Repositories\DatosInicialesRepository;
use App\Modules\DatosIniciales\Services\DatosInicialesService;

class DatosInicialesServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== DATOS INICIALES SERVICE TEST ==========\n\n";

        $this->testGetPrincipalBranchDataReturnsRows();
        $this->testEmptyPrincipalBranchFails();
        $this->testChangePasswordWithSessionValidatesConfirmation();
        $this->testSetPasswordByUserCodeReturnsPayload();
        $this->testGenerateSimulationPdfReturnsResults();
        $this->testAssignQuestionsToFormRequiresQuestions();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testGetPrincipalBranchDataReturnsRows(): void
    {
        echo "TEST 1: getPrincipalBranchData returns rows... ";

        try {
            $repository = new class extends DatosInicialesRepository {
                public function __construct()
                {
                }

                public function findPrincipalBranchByDescription(string $description): array
                {
                    return [
                        ['id' => 1, 'descripcion' => $description, 'nombre' => 'Principal'],
                    ];
                }
            };

            $service = new DatosInicialesService($repository);
            $result = $service->getPrincipalBranchData();

            if (($result['branches'][0]['nombre'] ?? '') !== 'Principal') {
                throw new \Exception('Unexpected branch name');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testChangePasswordWithSessionValidatesConfirmation(): void
    {
        echo "TEST 3: changePasswordWithSession validates confirmation... ";

        try {
            $service = new DatosInicialesService(new class extends DatosInicialesRepository {
                public function __construct()
                {
                }
            });

            $service->changePasswordWithSession('old', 'new', 'other', 'session-key');
            throw new \Exception('Expected exception was not thrown');
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Error de datos - Las contraseñas ingresadas no coinciden') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testSetPasswordByUserCodeReturnsPayload(): void
    {
        echo "TEST 4: setPasswordByUserCode returns payload... ";

        try {
            $service = new DatosInicialesService(new class extends DatosInicialesRepository {
                public function __construct()
                {
                }
            });

            $result = $service->setPasswordByUserCode(25, 'new-pass', 'new-pass');

            if (($result['userCode'] ?? 0) !== 25) {
                throw new \Exception('Unexpected userCode');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testGenerateSimulationPdfReturnsResults(): void
    {
        echo "TEST 5: generateSimulationPdf returns results... ";

        try {
            $service = new DatosInicialesService(new class extends DatosInicialesRepository {
                public function __construct()
                {
                }
            });

            $result = $service->generateSimulationPdf();

            if (($result['count'] ?? 0) <= 0) {
                throw new \Exception('Expected at least one result');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Throwable $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testAssignQuestionsToFormRequiresQuestions(): void
    {
        echo "TEST 6: assignQuestionsToForm validates questions... ";

        try {
            $service = new DatosInicialesService(new class extends DatosInicialesRepository {
                public function __construct()
                {
                }
            });

            $service->assignQuestionsToForm(1, 12, []);
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

    private function testEmptyPrincipalBranchFails(): void
    {
        echo "TEST 2: empty principal branch result is rejected... ";

        try {
            $repository = new class extends DatosInicialesRepository {
                public function __construct()
                {
                }

                public function findPrincipalBranchByDescription(string $description): array
                {
                    return [];
                }
            };

            $service = new DatosInicialesService($repository);
            $service->getPrincipalBranchData();
            throw new \Exception('Expected exception was not thrown');
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Error de datos, No existen valores iniciales para consultar') {
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

(new DatosInicialesServiceTest())->run();
