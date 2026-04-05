<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Http\Request;

class AdminParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run(): void
    {
        echo "\n========== ADMIN PARAMETERS TEST ==========\n\n";

        $this->testLegacyParameterParsing();
        $this->testParameterDefaults();
        $this->testActionParameter();
        $this->testLegacyAdminActionParameters();

        $this->printSummary();
        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testLegacyParameterParsing(): void
    {
        echo "TEST 1: Admin legacy parameters are parsed... ";

        try {
            $request = new Request('POST', [], [
                '_estado' => 'A',
                '_login' => 'testuser',
                '_nombre1' => 'Juan',
                '_apellido1' => 'Pérez',
                '_mail' => 'juan@example.com',
                '_id_perfil' => '1',
                '_id' => '123'
            ], [], []);

            if ($request->input('estado', 'A') !== 'A') {
                throw new \Exception('estado mismatch');
            }

            if ($request->input('login', '') !== 'testuser') {
                throw new \Exception('login mismatch');
            }

            if ((int) $request->input('id', 0) !== 123) {
                throw new \Exception('id mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testParameterDefaults(): void
    {
        echo "TEST 2: Admin parameter defaults are preserved... ";

        try {
            $request = new Request('POST', [], [], [], []);

            if ($request->input('estado', 'A') !== 'A') {
                throw new \Exception('estado default mismatch');
            }

            if ((int) $request->input('id_perfil', 1) !== 1) {
                throw new \Exception('id_perfil default mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testActionParameter(): void
    {
        echo "TEST 3: Admin action is detected... ";

        try {
            $request = new Request('POST', [], ['action' => 'OBTENER_USUARIOS'], [], []);

            if ($request->action() !== 'OBTENER_USUARIOS') {
                throw new \Exception('action mismatch');
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testLegacyAdminActionParameters(): void
    {
        echo "TEST 4: Admin legacy action parameters are parsed... ";

        try {
            $request = new Request('POST', [], [
                'action' => 'GET_ALL_RECURSOS_BY_PERFIL',
                '_idPerfil' => '2',
                '_perfil' => '3',
                '_recursos' => [
                    ['id' => 10, 'seleccionado' => true],
                ],
                '_arraydatos' => [
                    'nombre' => 'Traslado caja',
                    'tipo' => 'TRASLADO',
                    'cuentas' => [
                        ['idCuenta' => 1, 'tipo' => 'ORIGEN'],
                    ],
                ],
                '_operacion' => [
                    'nombre' => 'Operacion manual',
                ],
            ], [], []);

            if ((int) $request->input('idPerfil', 0) !== 2) {
                throw new \Exception('idPerfil mismatch');
            }

            if ((int) $request->input('perfil', 0) !== 3) {
                throw new \Exception('perfil mismatch');
            }

            $arraydatos = $request->input('arraydatos', []);
            if (($arraydatos['nombre'] ?? '') !== 'Traslado caja') {
                throw new \Exception('arraydatos.nombre mismatch');
            }

            $operacion = $request->input('operacion', []);
            if (($operacion['nombre'] ?? '') !== 'Operacion manual') {
                throw new \Exception('operacion.nombre mismatch');
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

(new AdminParametersTest())->run();
