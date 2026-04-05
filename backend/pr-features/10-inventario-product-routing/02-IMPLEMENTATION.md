# Feature-10: Inventario Product Routing - IMPLEMENTATION

## Alcance Implementado
- Se amplió `config/inventario-actions.php` con las acciones de catálogo/productos realmente invocadas por componentes.
- `InventarioController` ahora responde en formato legacy raíz para compatibilidad directa con pantallas existentes.
- `InventarioService` agrega handlers para catálogo, búsqueda, devolución, creación/actualización de producto y actividad de descuentos.
- Se ajustó compatibilidad de precargue para `_ingreso` y `_bodega_ingreso`.

## Archivos Modificados
- `config/inventario-actions.php`
- `app/Modules/Inventario/InventarioController.php`
- `app/Modules/Inventario/Services/InventarioService.php`

## Acciones Nuevas Cubiertas
- `SET_ACTIVIDAD_DESCUENTO`
- `INSERTAR_NUEVO_PRODUCTO`
- `ACTULIZAR_PRODUCTO`
- `BUSCAR_TODOS_LOS_PRODUCTOS`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE`
- `BUSCAR_PRODUCTO`
- `BUSCAR_EXISTENCIA_PRODUCTO`
- `BUSCAR_PRODUCTO_COD_BARRAS`
- `devolver_producto_venta`

## Ajustes de Compatibilidad
- `cancelPrechart()` acepta `_bodega_ingreso`.
- `savePrechart()` acepta `_ingreso`.
- El módulo deja de envolver respuesta con `Response::ok()` y entrega payload legacy plano para no romper verificaciones del frontend como `respuesta.error` y `respuesta.numdata`.

## No Migrado en Este Slice
- `TRASLADO_ENTRE_BODEGAS`
- `GET_CATEGORIAS`
- `GET_BODEGAS`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA`

Motivo:
No se encontró invocación directa desde componentes actuales del frontend al endpoint `/inventario/` para esas acciones.
