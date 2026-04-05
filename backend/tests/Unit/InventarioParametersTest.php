<?php
/**
 * tests/Unit/InventarioParametersTest.php
 * 
 * Tests para validar:
 * 1. Mapeo de acciones legacy
 * 2. Carga en Routes.php
 * 3. Controller existe
 * 4. Parámetros legacy
 * 5. Acción detectada
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class InventarioParametersTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    public function run()
    {
        echo "\n========== INVENTARIO PARAMETERS TEST ==========\n\n";

        $this->testInventarioActionsMapping();
        $this->testRoutesLoadsInventarioActions();
        $this->testInventarioControllerExists();
        $this->testAllVisibleLegacyActionsAreMapped();
        $this->testStockMoveAcceptsLegacyParameters();
        $this->testActionDetected();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testInventarioActionsMapping(): void
    {
        echo "TEST 1: Inventario actions are correctly mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/inventario-actions.php';

            $expectedActions = [
                'STOCK_MOVE',
                'STOCK_MOVE_DEVOLUCION',
                'TRASLADO_ENTRE_BODEGAS',
                'GET_CATEGORIAS',
                'BORRAR_DATOS_INGRESO_AUX_INVENTARIO',
                'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
                'GET_BODEGAS',
                'SET_ACTIVIDAD_DESCUENTO',
                'INSERTAR_NUEVO_PRODUCTO',
                'ACTULIZAR_PRODUCTO',
                'BUSCAR_TODOS_LOS_PRODUCTOS',
                'BUSCAR_TODOS_LOS_PRODUCTOS_OLD',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE',
                'BUSCAR_PRODUCTO',
                'BUSCAR_EXISTENCIA_PRODUCTO',
                'BUSCAR_PRODUCTO_COD_BARRAS',
                'devolver_producto_venta',
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

    private function testRoutesLoadsInventarioActions(): void
    {
        echo "TEST 2: Routes loads inventario actions correctly... ";

        try {
            $map = \App\Bootstrap\Routes::map();

            $expectedActions = [
                'STOCK_MOVE',
                'STOCK_MOVE_DEVOLUCION',
                'TRASLADO_ENTRE_BODEGAS',
                'GET_CATEGORIAS',
                'BORRAR_DATOS_INGRESO_AUX_INVENTARIO',
                'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
                'GET_BODEGAS',
                'SET_ACTIVIDAD_DESCUENTO',
                'INSERTAR_NUEVO_PRODUCTO',
                'ACTULIZAR_PRODUCTO',
                'BUSCAR_TODOS_LOS_PRODUCTOS',
                'BUSCAR_TODOS_LOS_PRODUCTOS_OLD',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE',
                'BUSCAR_PRODUCTO',
                'BUSCAR_EXISTENCIA_PRODUCTO',
                'BUSCAR_PRODUCTO_COD_BARRAS',
                'devolver_producto_venta',
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

    private function testInventarioControllerExists(): void
    {
        echo "TEST 3: InventarioController has required methods... ";

        try {
            $controller = \App\Modules\Inventario\InventarioController::class;

            $methods = [
                'recordStockMove',
                'recordStockMoveDevolución',
                'transferBetweenWarehouses',
                'getCategories',
                'cancelPrechart',
                'savePrechart',
                'getWarehouses',
                'createDiscountActivity',
                'createProduct',
                'updateProduct',
                'getAllProducts',
                'getAllProductsOld',
                'getProductsByCategory',
                'getProductsByBrand',
                'getProductsByName',
                'getProductById',
                'getProductExistenceByDocument',
                'getProductByIdOrBarcode',
                'returnProductSale',
            ];

            foreach ($methods as $method) {
                if (!method_exists($controller, $method)) {
                    throw new \Exception("Method $method not found in InventarioController");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testAllVisibleLegacyActionsAreMapped(): void
    {
        echo "TEST 4: all visible legacy inventario actions are mapped... ";

        try {
            $actions = require __DIR__ . '/../../config/inventario-actions.php';
            $legacyActions = [
                'devolver_producto_venta',
                'TRASLADO_ENTRE_BODEGAS',
                'GET_CATEGORIAS',
                'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
                'GET_BODEGAS',
                'BORRAR_DATOS_INGRESO_AUX_INVENTARIO',
                'BUSCAR_TODOS_LOS_PRODUCTOS',
                'BUSCAR_TODOS_LOS_PRODUCTOS_OLD',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA',
                'BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE',
                'BUSCAR_PRODUCTO',
                'BUSCAR_EXISTENCIA_PRODUCTO',
                'BUSCAR_PRODUCTO_COD_BARRAS',
                'INSERTAR_NUEVO_PRODUCTO',
                'ACTULIZAR_PRODUCTO',
                'SET_ACTIVIDAD_DESCUENTO',
            ];

            foreach ($legacyActions as $action) {
                if (!array_key_exists($action, $actions)) {
                    throw new \Exception("Missing visible legacy action: $action");
                }
            }

            echo "✓ PASSED\n";
            $this->passCount++;
        } catch (\Exception $e) {
            echo "✗ FAILED: {$e->getMessage()}\n";
            $this->failCount++;
        }
    }

    private function testStockMoveAcceptsLegacyParameters(): void
    {
        echo "TEST 5: inventario actions accept real legacy parameters... ";

        try {
            $body = [
                'action' => 'INGRESO_DATOS_DATOS_AUX_INVENTARIO',
                '_ingreso' => [
                    'idProducto' => 567,
                    'cantidad' => 10,
                    'bodega' => ['id' => 2, 'nombre' => 'Principal'],
                ],
                '_bodega_ingreso' => 2,
            ];

            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $ingreso = $request->input('ingreso', []);
            $warehouseId = $request->input('bodega_ingreso', 0);

            if (!is_array($ingreso) || ($ingreso['idProducto'] ?? 0) !== 567) {
                throw new \Exception("Failed to extract _ingreso");
            }

            if ($warehouseId !== 2) {
                throw new \Exception("Failed to extract _bodega_ingreso");
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
        echo "TEST 6: Action is detected correctly... ";

        try {
            $body = ['action' => 'STOCK_MOVE'];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);
            $action = $request->action();

            if ($action !== 'STOCK_MOVE') {
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

$test = new InventarioParametersTest();
$test->run();
