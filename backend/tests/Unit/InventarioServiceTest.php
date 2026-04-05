<?php
/**
 * tests/Unit/InventarioServiceTest.php
 * 
 * Tests para validar:
 * 1. InventarioService existe
 * 2. Estructura de respuesta
 * 3. Error handling
 * 4. Tipos de datos
 * 5. Autenticación requerida
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class InventarioServiceTest
{
    private int $passCount = 0;
    private int $failCount = 0;

    private function createMockAuthContext(?array $user): object
    {
        return new class($user) {
            private $userData;

            public function __construct($data)
            {
                $this->userData = $data;
            }

            public function user(): ?array
            {
                return $this->userData;
            }

            public function resolve($request): array
            {
                return $this->userData === null
                    ? ['success' => false]
                    : ['success' => true, 'compact_user' => ['nombre' => $this->userData['USUARIO'] ?? 'admin']];
            }
        };
    }

    public function run()
    {
        echo "\n========== INVENTARIO SERVICE TEST ==========\n\n";

        $this->testInventarioServiceExists();
        $this->testResponseStructureSuccess();
        $this->testResponseStructureError();
        $this->testProductsFiltersAndCatalogs();
        $this->testServiceRequiresAuthentication();

        $this->printSummary();

        exit($this->failCount === 0 ? 0 : 1);
    }

    private function testInventarioServiceExists(): void
    {
        echo "TEST 1: InventarioService exists and has methods... ";

        try {
            $service = \App\Modules\Inventario\Services\InventarioService::class;

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
                if (!method_exists($service, $method)) {
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

    private function testResponseStructureSuccess(): void
    {
        echo "TEST 2: Response has correct standard payload structure... ";

        try {
            $body = ['action' => 'STOCK_MOVE', 'id_documento' => 123];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $mockAuthContext = $this->createMockAuthContext(['USUARIO' => 'admin']);

            $service = new \App\Modules\Inventario\Services\InventarioService($request, $mockAuthContext);
            $result = $service->recordStockMove(123, 567, 10, 'salida');

            if (!is_array($result)) {
                throw new \Exception("recordStockMove did not return array");
            }

            if (!isset($result['movement']) || !is_array($result['movement'])) {
                throw new \Exception("Response missing movement");
            }

            if (($result['movement']['productId'] ?? null) !== 567) {
                throw new \Exception("Response missing productId");
            }

            if (!array_key_exists('message', $result)) {
                throw new \Exception("Response missing message");
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

            $mockAuthContext = $this->createMockAuthContext(null);

            $service = new \App\Modules\Inventario\Services\InventarioService($request, $mockAuthContext);

            try {
                $service->recordStockMove(0, 0, 0);
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

    private function testProductsFiltersAndCatalogs(): void
    {
        echo "TEST 4: inventario catalogs and filters return expected payloads... ";

        try {
            $body = [];
            $request = new \App\Core\Http\Request('POST', [], $body, [], []);

            $mockAuthContext = $this->createMockAuthContext(['USUARIO' => 'admin']);

            $service = new \App\Modules\Inventario\Services\InventarioService($request, $mockAuthContext);
            $catalogs = $service->getCategories();
            if (($catalogs['count'] ?? 0) <= 0) {
                throw new \Exception("Categories response is empty");
            }

            $productsByCategory = $service->getProductsByCategory(10, [0, 10]);
            if (($productsByCategory['count'] ?? 0) !== 1) {
                throw new \Exception("Expected one product in category 10");
            }

            $productsByBrand = $service->getProductsByBrand(21, [0, 10]);
            if (($productsByBrand['count'] ?? 0) !== 1) {
                throw new \Exception("Expected one product in brand 21");
            }

            $productExistence = $service->getProductExistenceByDocument('101', 1);
            if (($productExistence['productExistence']['idProducto'] ?? null) !== '101') {
                throw new \Exception("Expected product existence payload");
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

            $service = new \App\Modules\Inventario\Services\InventarioService($request, $mockAuthContext);

            $methods = [
                ['recordStockMove', [0, 0, 0]],
                ['recordStockMoveDevolución', [0, 0, 0]],
                ['transferBetweenWarehouses', [1, 2, '101', 1]],
                ['getCategories', []],
                ['cancelPrechart', [0]],
                ['savePrechart', [[], 0]],
                ['getWarehouses', []],
                ['createDiscountActivity', [['nombre' => 'Promo']]],
                ['createProduct', [['nombre' => 'Producto']]],
                ['updateProduct', [['nombre' => 'Producto']]],
                ['getAllProducts', [[]]],
                ['getAllProductsOld', [[]]],
                ['getProductsByCategory', [10, []]],
                ['getProductsByBrand', [20, []]],
                ['getProductsByName', ['shampoo', []]],
                ['getProductById', ['101']],
                ['getProductExistenceByDocument', ['101', 1]],
                ['getProductByIdOrBarcode', ['770101']],
                ['returnProductSale', [['idProducto' => 101]]],
            ];

            foreach ($methods as [$method, $args]) {
                try {
                    $service->$method(...$args);
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

$test = new InventarioServiceTest();
$test->run();
