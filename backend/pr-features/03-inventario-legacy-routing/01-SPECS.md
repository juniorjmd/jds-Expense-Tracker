# Feature-03: Inventario Legacy Routing - SPECS

## Objetivo

Cerrar la cobertura visible del modulo `inventario` del backend legacy y exponer todas sus respuestas a traves del envelope estandar `ok/data/error`.

## Acciones legacy visibles a cubrir

- `STOCK_MOVE`
- `STOCK_MOVE_DEVOLUCION`
- `TRASLADO_ENTRE_BODEGAS`
- `GET_CATEGORIAS`
- `INGRESO_DATOS_DATOS_AUX_INVENTARIO`
- `GET_BODEGAS`
- `BORRAR_DATOS_INGRESO_AUX_INVENTARIO`
- `BUSCAR_TODOS_LOS_PRODUCTOS`
- `BUSCAR_TODOS_LOS_PRODUCTOS_OLD`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA`
- `BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE`
- `BUSCAR_PRODUCTO`
- `BUSCAR_EXISTENCIA_PRODUCTO`
- `BUSCAR_PRODUCTO_COD_BARRAS`
- `INSERTAR_NUEVO_PRODUCTO`
- `ACTULIZAR_PRODUCTO`
- `SET_ACTIVIDAD_DESCUENTO`
- `devolver_producto_venta`

## Contrato backend requerido

- exito:
  - `ok: true`
  - `data: {...}`
  - `error: null`
- error:
  - `ok: false`
  - `data: null`
  - `error.code`
  - `error.message`

## Regla de alineacion con frontend

- el backend conserva compatibilidad de entrada legacy
- el frontend deja de leer la respuesta HTTP legacy cruda
- `ProductoService` se vuelve la capa de adaptacion del modulo

## Criterios de aceptacion

1. Todas las acciones visibles del `inventario/index.php` legacy quedan mapeadas.
2. `InventarioController` responde solo con `Response::ok()` y `Response::fail()`.
3. `InventarioService` ya no devuelve payload raiz con `error/numdata/data` mezclados.
4. `ProductoService` desempaqueta el envelope estandar y entrega datos normalizados a los componentes.
5. Existen pruebas de backend y frontend para el modulo.
