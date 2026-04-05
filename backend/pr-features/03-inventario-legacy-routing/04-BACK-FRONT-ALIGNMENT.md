# Inventario Back Front Alignment

## Backend

- modulo revisado: `Inventario`
- estado final de esta pasada: completo por acciones visibles del legacy
- contrato HTTP final:
  - `ok/data/error`
- acciones visibles cubiertas:
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

## Frontend

Archivos principales alineados:

- `src/app/services/producto.service.ts`
- `src/app/interfaces/inventario-response.interface.ts`
- `src/app/services/producto.service.spec.ts`

Consumidores directos confirmados:

- `src/app/modules/admin/pages/productos/productos.component.ts`
- `src/app/modules/pos/pages/ventas/ventas.component.ts`
- `src/app/modules/pos/modals/ingresar-producto-venta/ingresar-producto-venta.component.ts`
- `src/app/modules/pos/modals/ModalUpdateProductoVenta/ModalUpdateProductoVenta.component.ts`
- `src/app/modules/compras/pages/crear/crearCompra.component.ts`
- `src/app/modules/compras/pages/editar/editarCompra.component.ts`
- `src/app/modules/admin/modals/modalUpdateProducto/modalUpdateProducto.component.ts`

## Criterio de adaptacion

- el frontend ya no depende del payload HTTP legacy del backend
- la compatibilidad transitoria con `error/numdata/data` queda encapsulada dentro de `ProductoService`

## PRs esperados

- backend:
  - `feature/documentos-legacy-routing` -> PR de cierre de `Inventario`
- frontend:
  - `dev` -> PR de alineacion de `Inventario`

## Validacion real local

Fecha de validacion: `2026-04-04`

Entorno:

- frontend local: `http://localhost/jds_carwash/`
- backend local: `http://localhost/jds_back_2026/api/`
- base de datos real conectada desde `.env`

Pruebas ejecutadas:

- `GET_CATEGORIAS` desde flujo real de frontend
- `POST http://localhost/jds_back_2026/api/inventario/` con consumo real del modulo

Resultado:

- el endpoint responde con control de autenticacion real
- se detecto que en Apache local la cabecera `Authorization` no estaba llegando de forma confiable al backend
- se alineo el contrato de autenticacion aceptando tambien `X-Session-Token`
- el frontend quedo enviando tanto `Authorization` como `X-Session-Token`
- `GET_CATEGORIAS` ya respondio correctamente con sesion real despues del ajuste
- conclusion:
  - el contrato del endpoint ya esta vivo
  - el flujo autenticado del modulo queda estable en este entorno local
