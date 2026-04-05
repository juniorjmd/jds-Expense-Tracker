<?php
declare(strict_types=1);

namespace App\Modules\Inventario\Services;

use App\Core\Http\Request;

class InventarioService
{
    private Request $request;
    private $authContext;

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    public function recordStockMove(
        int $documentId,
        int $productId,
        float $quantity,
        string $movementType = 'salida'
    ): array {
        $this->resolveAuthenticatedUser();

        return [
            'message' => 'Movimiento de stock registrado correctamente',
            'movement' => [
                'id' => 1,
                'documentId' => $documentId,
                'productId' => $productId,
                'quantity' => $quantity,
                'movementType' => $movementType,
                'previousQuantity' => 100,
                'currentQuantity' => $movementType === 'salida' ? 100 - $quantity : 100 + $quantity,
                'performedAt' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public function recordStockMoveDevolución(
        int $documentId,
        int $productId,
        float $quantity
    ): array {
        $this->resolveAuthenticatedUser();

        return [
            'message' => 'Devolucion de stock registrada correctamente',
            'movement' => [
                'id' => 1,
                'documentId' => $documentId,
                'productId' => $productId,
                'quantity' => $quantity,
                'movementType' => 'devolucion',
                'previousQuantity' => 40,
                'currentQuantity' => 40 + $quantity,
                'performedAt' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public function transferBetweenWarehouses(
        int $sourceWarehouseId,
        int $targetWarehouseId,
        string $productId,
        float $quantity,
        int $userId = 0
    ): array {
        $this->resolveAuthenticatedUser();

        if ($sourceWarehouseId <= 0 || $targetWarehouseId <= 0 || trim($productId) === '' || $quantity <= 0) {
            throw new \Exception('Todos los datos son necesarios para realizar la operacion');
        }

        return [
            'message' => 'Traslado entre bodegas registrado correctamente',
            'transfer' => [
                'sourceWarehouseId' => $sourceWarehouseId,
                'targetWarehouseId' => $targetWarehouseId,
                'productId' => $productId,
                'quantity' => $quantity,
                'userId' => $userId,
                'status' => 'OK',
            ],
        ];
    }

    public function getCategories(): array
    {
        $this->resolveAuthenticatedUser();

        $categories = [
            [
                'id' => 10,
                'letra' => 'A',
                'nombre' => 'Productos de lavado',
                'descripcion' => 'Categoria principal de lavado',
                'tipo' => 'PRD',
                'contador' => 4,
                'numHijos' => 0,
            ],
            [
                'id' => 11,
                'letra' => 'B',
                'nombre' => 'Servicios',
                'descripcion' => 'Servicios prestados en punto de venta',
                'tipo' => 'SRV',
                'contador' => 2,
                'numHijos' => 0,
            ],
        ];

        return [
            'categories' => $categories,
            'count' => count($categories),
        ];
    }

    public function cancelPrechart(int $ingressId, int $warehouseId = 0): array
    {
        $usuario = $this->resolveAuthenticatedUser();

        return [
            'message' => 'Precargue cancelado correctamente',
            'status' => 'CANCELADO',
            'ingressId' => $ingressId,
            'warehouseId' => $warehouseId,
            'items' => [],
            'count' => 0,
            'performedAt' => date('Y-m-d H:i:s'),
            'user' => $usuario['nombre'] ?? 'anonymous',
        ];
    }

    public function savePrechart(array $items, int $ingressId, array $ingressPayload = []): array
    {
        $usuario = $this->resolveAuthenticatedUser();

        if ($ingressPayload !== []) {
            $items = [[
                'id' => 1,
                'idProducto' => $ingressPayload['idProducto'] ?? $ingressPayload['id_producto'] ?? null,
                'cantidad' => $ingressPayload['cantidad'] ?? 0,
                'bodega' => $ingressPayload['bodega']['id'] ?? null,
                'nombreBodega' => $ingressPayload['bodega']['nombre'] ?? '',
                'usuario' => $usuario['nombre'] ?? 'anonymous',
            ]];
        }

        return [
            'message' => 'Precargue guardado correctamente',
            'status' => 'GUARDADO',
            'ingressId' => $ingressId,
            'items' => array_values($items),
            'count' => count($items),
            'performedAt' => date('Y-m-d H:i:s'),
            'user' => $usuario['nombre'] ?? 'anonymous',
        ];
    }

    public function getWarehouses(): array
    {
        $this->resolveAuthenticatedUser();

        $warehouses = [
            [
                'id' => 1,
                'nombre' => 'Bodega principal',
                'descripcion' => 'Bodega principal del sistema',
                'tipo' => 1,
                'tipo_descripcion' => 'Principal',
            ],
            [
                'id' => 2,
                'nombre' => 'Bodega secundaria',
                'descripcion' => 'Bodega de apoyo',
                'tipo' => 2,
                'tipo_descripcion' => 'Secundaria',
            ],
        ];

        return [
            'warehouses' => $warehouses,
            'count' => count($warehouses),
        ];
    }

    public function createDiscountActivity(array $data): array
    {
        $this->resolveAuthenticatedUser();

        if (trim((string) ($data['nombre'] ?? '')) === '') {
            throw new \Exception('Debe enviar el nombre de la actividad');
        }

        return [
            'message' => 'Actividad de descuento creada correctamente',
            'activityId' => 1,
            'activity' => $data,
        ];
    }

    public function createProduct(array $product): array
    {
        $this->resolveAuthenticatedUser();
        $this->validateProductPayload($product);

        return [
            'message' => 'Producto creado correctamente',
            'product' => $product,
            'count' => 1,
        ];
    }

    public function updateProduct(array $product): array
    {
        $this->resolveAuthenticatedUser();
        $this->validateProductPayload($product);

        return [
            'message' => 'Producto actualizado correctamente',
            'product' => $product,
            'count' => 1,
        ];
    }

    public function getAllProducts(array $limit = []): array
    {
        return $this->buildProductsResponse(
            $this->applyLimit($this->sampleProducts(), $limit),
            'sample_products'
        );
    }

    public function getAllProductsOld(array $limit = []): array
    {
        return $this->buildProductsResponse(
            $this->applyLimit($this->sampleProducts(), $limit),
            'sample_products_old'
        );
    }

    public function getProductsByCategory(int $categoryId, array $limit = []): array
    {
        $this->resolveAuthenticatedUser();

        if ($categoryId <= 0) {
            throw new \Exception('Codigo de categoria invalido');
        }

        $products = array_values(array_filter(
            $this->sampleProducts(),
            static fn (array $product): bool => (int) ($product['idCategoria'] ?? 0) === $categoryId
        ));

        return $this->buildProductsResponse($this->applyLimit($products, $limit), 'sample_products_by_category');
    }

    public function getProductsByBrand(int $brandId, array $limit = []): array
    {
        $this->resolveAuthenticatedUser();

        if ($brandId <= 0) {
            throw new \Exception('Codigo de marca-producto invalido');
        }

        $products = array_values(array_filter(
            $this->sampleProducts(),
            static fn (array $product): bool => (int) ($product['idMarca'] ?? 0) === $brandId
        ));

        return $this->buildProductsResponse($this->applyLimit($products, $limit), 'sample_products_by_brand');
    }

    public function getProductsByName(string $searchText, array $limit = []): array
    {
        $this->resolveAuthenticatedUser();

        $needle = mb_strtolower(trim($searchText));
        $products = array_values(array_filter(
            $this->sampleProducts(),
            static function (array $product) use ($needle): bool {
                if ($needle === '') {
                    return true;
                }

                return str_contains(mb_strtolower((string) ($product['nombre'] ?? '')), $needle)
                    || str_contains(mb_strtolower((string) ($product['barcode'] ?? '')), $needle);
            }
        ));

        return $this->buildProductsResponse($this->applyLimit($products, $limit), 'sample_products_by_name');
    }

    public function getProductById(string $productId): array
    {
        $this->resolveAuthenticatedUser();

        if (trim($productId) === '') {
            throw new \Exception('Falta el id del producto a validar');
        }

        $product = $this->findSampleProductByIdOrBarcode($productId);
        $products = $product === null ? [] : [$product];

        return [
            'product' => $product ?? [],
            'products' => $products,
            'count' => count($products),
            'query' => 'sample_product_by_id',
        ];
    }

    public function getProductExistenceByDocument(string $productId, int $documentOrder): array
    {
        $this->resolveAuthenticatedUser();

        if (trim($productId) === '' || $documentOrder <= 0) {
            throw new \Exception('Falta el id del producto a validar');
        }

        $product = $this->findSampleProductByIdOrBarcode($productId);
        $existence = $product['existencias'][0]['cant_actual'] ?? 0;
        $warehouseName = $product['existencias'][0]['nombreBodega'] ?? 'Bodega principal';
        $warehouseId = $product['existencias'][0]['id_bodega'] ?? 1;

        return [
            'productExistence' => [
                'nombreBodega' => $warehouseName,
                'idProducto' => $productId,
                'existencia' => $existence,
                'idBodega' => $warehouseId,
                'ordenDocumento' => $documentOrder,
            ],
            'count' => $product === null ? 0 : 1,
            'query' => 'sample_product_existence',
        ];
    }

    public function getProductByIdOrBarcode(string $productId): array
    {
        $this->resolveAuthenticatedUser();

        if (trim($productId) === '') {
            throw new \Exception('Falta el id del producto a validar');
        }

        $product = $this->findSampleProductByIdOrBarcode($productId);
        $products = $product !== null ? [$product] : [];

        return [
            'products' => $products,
            'count' => count($products),
            'query' => 'sample_product_by_id_or_barcode',
        ];
    }

    public function returnProductSale(mixed $product): array
    {
        $this->resolveAuthenticatedUser();

        $line = is_array($product) ? $product : [];

        return [
            'message' => 'Producto devuelto correctamente',
            'status' => 'DEVUELTO',
            'product' => $line,
        ];
    }

    private function buildProductsResponse(array $products, string $query): array
    {
        $this->resolveAuthenticatedUser();

        return [
            'products' => array_values($products),
            'count' => count($products),
            'query' => $query,
        ];
    }

    private function resolveAuthenticatedUser(): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }

        return $authResult['compact_user'] ?? [];
    }

    private function validateProductPayload(array $product): void
    {
        if (trim((string) ($product['nombre'] ?? '')) === '') {
            throw new \Exception('Debe enviar el nombre del producto');
        }
    }

    private function sampleProducts(): array
    {
        return [
            [
                'id' => 101,
                'nombre' => 'Shampoo Premium',
                'barcode' => '770101',
                'tipo_producto' => 1,
                'idCategoria' => 10,
                'idMarca' => 20,
                'porcent_iva' => 19,
                'images' => [],
                'precios' => [[
                    'id_producto' => 101,
                    'precio_con_iva' => 12000,
                    'precio_antes_de_iva' => 10084.03,
                    'valor_iva' => 1915.97,
                ]],
                'existencias' => [[
                    'id_bodega' => 1,
                    'nombreBodega' => 'Bodega principal',
                    'cant_actual' => 12,
                ]],
            ],
            [
                'id' => 202,
                'nombre' => 'Lavado Full',
                'barcode' => '770202',
                'tipo_producto' => 2,
                'idCategoria' => 11,
                'idMarca' => 21,
                'porcent_iva' => 0,
                'images' => [],
                'precios' => [[
                    'id_producto' => 202,
                    'precio_con_iva' => 25000,
                    'precio_antes_de_iva' => 25000,
                    'valor_iva' => 0,
                ]],
                'existencias' => [[
                    'id_bodega' => 1,
                    'nombreBodega' => 'Bodega principal',
                    'cant_actual' => 999,
                ]],
            ],
        ];
    }

    private function findSampleProductByIdOrBarcode(string $value): ?array
    {
        foreach ($this->sampleProducts() as $product) {
            if ((string) ($product['id'] ?? '') === trim($value) || (string) ($product['barcode'] ?? '') === trim($value)) {
                return $product;
            }
        }

        return null;
    }

    private function applyLimit(array $items, array $limit): array
    {
        if (count($limit) >= 2) {
            return array_slice($items, (int) $limit[0], (int) $limit[1]);
        }

        if (count($limit) === 1) {
            return array_slice($items, 0, (int) $limit[0]);
        }

        return $items;
    }
}
