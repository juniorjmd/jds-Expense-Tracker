<?php
declare(strict_types=1);

namespace App\Modules\Inventario;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Inventario\Services\InventarioService;

class InventarioController
{
    private Request $request;
    private InventarioService $service;

    public function __construct(
        Request $request,
        InventarioService $service
    ) {
        $this->request = $request;
        $this->service = $service;
    }

    public function recordStockMove(): void
    {
        try {
            Response::ok($this->service->recordStockMove(
                documentId: (int) $this->request->input('id_documento', 0),
                productId: (int) $this->request->input('id_producto', 0),
                quantity: (float) $this->request->input('cantidad', 0),
                movementType: $this->request->input('tipo_movimiento', 'salida')
            ));
        } catch (\Exception $e) {
            Response::fail('STOCK_MOVE_ERROR', $e->getMessage());
        }
    }

    public function recordStockMoveDevolución(): void
    {
        try {
            Response::ok($this->service->recordStockMoveDevolución(
                documentId: (int) $this->request->input('id_documento', 0),
                productId: (int) $this->request->input('id_producto', 0),
                quantity: (float) $this->request->input('cantidad', 0)
            ));
        } catch (\Exception $e) {
            Response::fail('STOCK_MOVE_DEVOLUCION_ERROR', $e->getMessage());
        }
    }

    public function transferBetweenWarehouses(): void
    {
        try {
            Response::ok($this->service->transferBetweenWarehouses(
                sourceWarehouseId: (int) $this->request->input('id_bodega_origen', 0),
                targetWarehouseId: (int) $this->request->input('id_bodega_destino', 0),
                productId: (string) $this->request->input('id_producto', ''),
                quantity: (float) $this->request->input('cantidad', 0),
                userId: (int) $this->request->input('id_usuario', 0)
            ));
        } catch (\Exception $e) {
            Response::fail('TRASLADO_ENTRE_BODEGAS_ERROR', $e->getMessage());
        }
    }

    public function getCategories(): void
    {
        try {
            Response::ok($this->service->getCategories());
        } catch (\Exception $e) {
            Response::fail('GET_CATEGORIAS_ERROR', $e->getMessage());
        }
    }

    public function cancelPrechart(): void
    {
        try {
            Response::ok($this->service->cancelPrechart(
                ingressId: (int) $this->request->input('id_ingreso', 0),
                warehouseId: (int) $this->request->input('bodega_ingreso', 0)
            ));
        } catch (\Exception $e) {
            Response::fail('BORRAR_DATOS_INGRESO_AUX_INVENTARIO_ERROR', $e->getMessage());
        }
    }

    public function savePrechart(): void
    {
        try {
            $listado = $this->request->input('listado', '[]');
            if (is_string($listado)) {
                $listado = json_decode($listado, true) ?: [];
            }

            Response::ok($this->service->savePrechart(
                items: $listado,
                ingressId: (int) $this->request->input('id_ingreso', 0),
                ingressPayload: $this->normalizeIngreso($this->request->input('ingreso', []))
            ));
        } catch (\Exception $e) {
            Response::fail('INGRESO_DATOS_DATOS_AUX_INVENTARIO_ERROR', $e->getMessage());
        }
    }

    public function getWarehouses(): void
    {
        try {
            Response::ok($this->service->getWarehouses());
        } catch (\Exception $e) {
            Response::fail('GET_BODEGAS_ERROR', $e->getMessage());
        }
    }

    public function createDiscountActivity(): void
    {
        try {
            Response::ok($this->service->createDiscountActivity(
                $this->normalizeArray($this->request->input('datosInsert', []))
            ));
        } catch (\Exception $e) {
            Response::fail('SET_ACTIVIDAD_DESCUENTO_ERROR', $e->getMessage());
        }
    }

    public function createProduct(): void
    {
        try {
            Response::ok($this->service->createProduct(
                $this->normalizeProducto($this->request->input('producto_enviado', []))
            ));
        } catch (\Exception $e) {
            Response::fail('INSERTAR_NUEVO_PRODUCTO_ERROR', $e->getMessage());
        }
    }

    public function updateProduct(): void
    {
        try {
            Response::ok($this->service->updateProduct(
                $this->normalizeProducto($this->request->input('producto_enviado', []))
            ));
        } catch (\Exception $e) {
            Response::fail('ACTULIZAR_PRODUCTO_ERROR', $e->getMessage());
        }
    }

    public function getAllProducts(): void
    {
        try {
            Response::ok($this->service->getAllProducts($this->normalizeLimit($this->request->input('limit', []))));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_TODOS_LOS_PRODUCTOS_ERROR', $e->getMessage());
        }
    }

    public function getAllProductsOld(): void
    {
        try {
            Response::ok($this->service->getAllProductsOld($this->normalizeLimit($this->request->input('limit', []))));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_TODOS_LOS_PRODUCTOS_OLD_ERROR', $e->getMessage());
        }
    }

    public function getProductsByCategory(): void
    {
        try {
            Response::ok($this->service->getProductsByCategory(
                categoryId: (int) $this->request->input('id_cate', 0),
                limit: $this->normalizeLimit($this->request->input('limit', []))
            ));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA_ERROR', $e->getMessage());
        }
    }

    public function getProductsByBrand(): void
    {
        try {
            Response::ok($this->service->getProductsByBrand(
                brandId: (int) $this->request->input('id_brand', 0),
                limit: $this->normalizeLimit($this->request->input('limit', []))
            ));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA_ERROR', $e->getMessage());
        }
    }

    public function getProductsByName(): void
    {
        try {
            Response::ok($this->service->getProductsByName(
                searchText: (string) $this->request->input('dato_busqueda', ''),
                limit: $this->normalizeLimit($this->request->input('limit', []))
            ));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE_ERROR', $e->getMessage());
        }
    }

    public function getProductById(): void
    {
        try {
            Response::ok($this->service->getProductById((string) $this->request->input('id_producto', '')));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_PRODUCTO_ERROR', $e->getMessage());
        }
    }

    public function getProductExistenceByDocument(): void
    {
        try {
            Response::ok($this->service->getProductExistenceByDocument(
                productId: (string) $this->request->input('id_producto', ''),
                documentOrder: (int) $this->request->input('orden_documento', 0)
            ));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_EXISTENCIA_PRODUCTO_ERROR', $e->getMessage());
        }
    }

    public function getProductByIdOrBarcode(): void
    {
        try {
            Response::ok($this->service->getProductByIdOrBarcode((string) $this->request->input('id_producto', '')));
        } catch (\Exception $e) {
            Response::fail('BUSCAR_PRODUCTO_COD_BARRAS_ERROR', $e->getMessage());
        }
    }

    public function returnProductSale(): void
    {
        try {
            Response::ok($this->service->returnProductSale($this->request->input('producto_enviado', [])));
        } catch (\Exception $e) {
            Response::fail('DEVOLVER_PRODUCTO_VENTA_ERROR', $e->getMessage());
        }
    }

    private function normalizeIngreso(mixed $ingreso): array
    {
        return is_array($ingreso) ? $ingreso : [];
    }

    private function normalizeProducto(mixed $producto): array
    {
        return is_array($producto) ? $producto : [];
    }

    private function normalizeArray(mixed $payload): array
    {
        return is_array($payload) ? $payload : [];
    }

    private function normalizeLimit(mixed $limit): array
    {
        return is_array($limit) ? $limit : [];
    }
}
