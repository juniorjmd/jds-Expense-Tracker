<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;
use App\Modules\Admin\Services\AdminService;

class AdminServiceAuthContextStub
{
    public function resolve(Request $request): array
    {
        return [
            'success' => true,
            'compact_user' => ['id' => 1, 'nombre' => 'Admin User']
        ];
    }
}

class AdminServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== ADMIN SERVICE TEST ==========\n\n";

        $this->testGetUsersReturnsArray();
        $this->testGetAllResourcesReturnsTree();
        $this->testGetAllResourcesByProfileMarksSelection();
        $this->testSetProfileResourcesReturnsSelectedIds();
        $this->testCreateUserValidatesRequiredFields();
        $this->testCreateUserThrowsExceptionForMissingFields();
        $this->testUpdateUserValidatesUserId();
        $this->testUpdateUserThrowsExceptionForInvalidId();
        $this->testGetMenusReturnsArray();
        $this->testCreateManualOperationReturnsPayload();
        $this->testCreatePresetOperationRequiresAccounts();
        $this->testExecutePresetOperationReturnsPayload();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function service(): AdminService
    {
        return new AdminService(new Request('POST', [], [], [], []), new AdminServiceAuthContextStub());
    }

    private function testGetUsersReturnsArray(): void
    {
        echo "TEST 1: getUsers returns array... ";

        try {
            $result = $this->service()->getUsers();
            if (!is_array($result) || !isset($result[0]['ID'])) {
                throw new \Exception('invalid response');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testGetAllResourcesReturnsTree(): void
    {
        echo "TEST 2: getAllResources returns standard tree... ";

        try {
            $result = $this->service()->getAllResources();
            if (($result['count'] ?? 0) < 3 || !isset($result['resources'][1]['recursosHijos'])) {
                throw new \Exception('invalid resource tree');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testGetAllResourcesByProfileMarksSelection(): void
    {
        echo "TEST 3: getAllResourcesByProfile marks selected resources... ";

        try {
            $result = $this->service()->getAllResourcesByProfile(2);
            if (($result['profileId'] ?? 0) !== 2) {
                throw new \Exception('invalid profile id');
            }
            if (($result['resources'][1]['seleccionado'] ?? false) !== true) {
                throw new \Exception('expected selected admin resource');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testSetProfileResourcesReturnsSelectedIds(): void
    {
        echo "TEST 4: setProfileResources returns selected ids... ";

        try {
            $result = $this->service()->setProfileResources(2, [[
                'id' => 1,
                'seleccionado' => true,
                'recursosHijos' => [
                    ['id' => 2, 'seleccionado' => false, 'recursosHijos' => []],
                    ['id' => 3, 'seleccionado' => true, 'recursosHijos' => []],
                ],
            ]]);

            if (($result['updatedCount'] ?? 0) !== 2) {
                throw new \Exception('unexpected selected count');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateUserValidatesRequiredFields(): void
    {
        echo "TEST 5: createUser creates valid user... ";

        try {
            $result = $this->service()->createUser([
                'idPersona' => 25,
                'Login' => 'testuser',
                'Nombre1' => 'Test',
                'Apellido1' => 'User',
                'email' => 'test@example.com',
                'estado' => 1
            ]);
            if ((($result['usuario']['Login'] ?? '') !== 'testuser')) {
                throw new \Exception('user not created');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateUserThrowsExceptionForMissingFields(): void
    {
        echo "TEST 6: createUser validates required fields... ";

        try {
            $this->service()->createUser(['Login' => 'testuser']);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'La persona ingresada para usuario no existe')) {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testUpdateUserValidatesUserId(): void
    {
        echo "TEST 7: updateUser returns updated data... ";

        try {
            $result = $this->service()->updateUser(123, ['estado' => 'I']);
            if (($result['ID'] ?? 0) !== 123) {
                throw new \Exception('invalid updated id');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testUpdateUserThrowsExceptionForInvalidId(): void
    {
        echo "TEST 8: updateUser rejects invalid id... ";

        try {
            $this->service()->updateUser(0, ['estado' => 'A']);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'ID de usuario inválido') {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testGetMenusReturnsArray(): void
    {
        echo "TEST 9: getMenus returns array... ";

        try {
            $result = $this->service()->getMenus();
            if (!is_array($result) || !isset($result[0]['idmenus'])) {
                throw new \Exception('invalid menus response');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreateManualOperationReturnsPayload(): void
    {
        echo "TEST 10: createManualOperation returns payload... ";

        try {
            $result = $this->service()->createManualOperation([
                'nombre' => 'Ajuste contable',
                'descripcion' => 'Prueba',
                'idPersona' => 5,
                'totalDebito' => 1000,
                'totalCredito' => 1000,
                'fechaOperacion' => '2026-04-04',
            ]);

            if (($result['operationId'] ?? 0) <= 0) {
                throw new \Exception('invalid operation id');
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testCreatePresetOperationRequiresAccounts(): void
    {
        echo "TEST 11: createPresetOperation validates accounts... ";

        try {
            $this->service()->createPresetOperation(['nombre' => 'Traslado']);
            throw new \Exception('Expected exception was not thrown');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'No existen cuentas para agregar a la tranferencia')) {
                echo "✗ FAILED: {$e->getMessage()}\n";
                $this->failCount++;
                return;
            }
            echo "✓ PASSED\n";
            $this->passCount++;
        }
    }

    private function testExecutePresetOperationReturnsPayload(): void
    {
        echo "TEST 12: executePresetOperation returns payload... ";

        try {
            $result = $this->service()->executePresetOperation([
                'nombre' => 'Traslado principal',
                'tipo' => 'CAJA',
                'idEstablecimiento' => 1,
                'cuentas' => [
                    ['idCuenta' => 10, 'tipo' => 'ORIGEN', 'valor' => -1000],
                    ['idCuenta' => 11, 'tipo' => 'DESTINO', 'valor' => 1000],
                ],
            ]);

            if (($result['objeto']['idOperacion'] ?? 0) <= 0) {
                throw new \Exception('invalid execution payload');
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

(new AdminServiceTest())->run();
