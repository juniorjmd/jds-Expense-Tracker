# Feature-10: Inventario Product Routing - SPECS

## Objetivo
Ampliar el módulo `inventario` solo para las acciones legacy del catálogo/productos y precargue que el frontend actual sí invoca contra `/inventario/`.

## Requisitos Funcionales

### RF-01: Acciones usadas por UI
El sistema debe mapear:

| Acción | Método Esperado | Uso detectado |
|--------|-----------------|---------------|
| `SET_ACTIVIDAD_DESCUENTO` | `InventarioController::createDiscountActivity()` | crear actividad de descuento |
| `INSERTAR_NUEVO_PRODUCTO` | `InventarioController::createProduct()` | admin productos |
| `ACTULIZAR_PRODUCTO` | `InventarioController::updateProduct()` | modal actualización producto |
| `BUSCAR_TODOS_LOS_PRODUCTOS` | `InventarioController::getAllProducts()` | listado inicial productos |
| `BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE` | `InventarioController::getProductsByName()` | búsqueda admin productos |
| `BUSCAR_PRODUCTO` | `InventarioController::getProductById()` | modal ingresar producto |
| `BUSCAR_EXISTENCIA_PRODUCTO` | `InventarioController::getProductExistenceByDocument()` | modal edición línea |
| `BUSCAR_PRODUCTO_COD_BARRAS` | `InventarioController::getProductByIdOrBarcode()` | POS / compras / admin |
| `devolver_producto_venta` | `InventarioController::returnProductSale()` | eliminar línea de compra/venta |

### RF-02: Compatibilidad de respuesta
- Las respuestas del módulo `inventario` deben llegar en formato legacy raíz: `error`, `numdata`, `data`, `productos`, `datos`, etc.
- `BORRAR_DATOS_INGRESO_AUX_INVENTARIO` e `INGRESO_DATOS_DATOS_AUX_INVENTARIO` deben aceptar los parámetros reales que hoy envía el frontend (`_bodega_ingreso`, `_ingreso`).

### RF-03: Alcance explícito
- No se migran en esta feature acciones de inventario sin referencia real encontrada en componentes del frontend.
