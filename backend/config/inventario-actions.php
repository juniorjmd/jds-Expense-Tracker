<?php

/**
 * Mapea acciones legacy del módulo Inventario
 * 
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 */

use App\Modules\Inventario\InventarioController;

return [
    'STOCK_MOVE' => [InventarioController::class, 'recordStockMove'],
    'STOCK_MOVE_DEVOLUCION' => [InventarioController::class, 'recordStockMoveDevolución'],
    'TRASLADO_ENTRE_BODEGAS' => [InventarioController::class, 'transferBetweenWarehouses'],
    'GET_CATEGORIAS' => [InventarioController::class, 'getCategories'],
    'BORRAR_DATOS_INGRESO_AUX_INVENTARIO' => [InventarioController::class, 'cancelPrechart'],
    'INGRESO_DATOS_DATOS_AUX_INVENTARIO' => [InventarioController::class, 'savePrechart'],
    'GET_BODEGAS' => [InventarioController::class, 'getWarehouses'],
    'SET_ACTIVIDAD_DESCUENTO' => [InventarioController::class, 'createDiscountActivity'],
    'INSERTAR_NUEVO_PRODUCTO' => [InventarioController::class, 'createProduct'],
    'ACTULIZAR_PRODUCTO' => [InventarioController::class, 'updateProduct'],
    'BUSCAR_TODOS_LOS_PRODUCTOS' => [InventarioController::class, 'getAllProducts'],
    'BUSCAR_TODOS_LOS_PRODUCTOS_OLD' => [InventarioController::class, 'getAllProductsOld'],
    'BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA' => [InventarioController::class, 'getProductsByCategory'],
    'BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA' => [InventarioController::class, 'getProductsByBrand'],
    'BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE' => [InventarioController::class, 'getProductsByName'],
    'BUSCAR_PRODUCTO' => [InventarioController::class, 'getProductById'],
    'BUSCAR_EXISTENCIA_PRODUCTO' => [InventarioController::class, 'getProductExistenceByDocument'],
    'BUSCAR_PRODUCTO_COD_BARRAS' => [InventarioController::class, 'getProductByIdOrBarcode'],
    'devolver_producto_venta' => [InventarioController::class, 'returnProductSale'],
];
