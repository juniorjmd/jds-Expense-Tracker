# Feature-05: Documentos Module Legacy Routing - ACCEPTANCE_CRITERIA

## Criterios funcionales cerrados en este corte

### CA-01: Consultar documentos del usuario actual

- `GET_DOCUMENTOS_USUARIO_ACTUAL` responde con envelope estandar:
  - `ok`
  - `data.records`
  - `data.count`
  - `error`

### CA-02: Consultar documentos del usuario por caja activa

- `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA` responde con envelope estandar
- la llamada real local ya devolvio documentos del usuario autenticado

### CA-03: Crear documento de venta por usuario

- `CREAR_DOCUMENTO_POR_USUARIO` responde con envelope estandar
- incluye `data.message`
- incluye `data.records` y `data.count`
- validacion real local ejecutada contra backend Apache

### CA-04: Crear documento de compra por usuario

- `CREAR_DOCUMENTO_COMPRA_POR_USUARIO` queda registrado en backend nuevo
- requiere `_establecimiento` valido

### CA-05: Cambiar documento activo

- `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO` responde con:
  - `data.message`
  - `data.documentId`
- validacion real local ejecutada

### CA-06: Cambiar documento de compra activo

- `CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO` responde con:
  - `data.message`
  - `data.documentId`

## No regresion

- `php tests/run-tests.php` pasa completo
- el router sigue resolviendo acciones legacy por mapa
- el frontend de ventas ya no depende de `numdata/data/error` para `documentos`

## Pendientes para cierre total del modulo

- `CREAR_DOCUMENTO_GASTO_POR_USUARIO`
- `CERRAR_DOCUMENTO_FACTURA`
- `CERRAR_DOCUMENTO_REMISION`
- `CAMBIAR_DOCUMENTO_A_ENVIO`
- `CANCELAR_DOCUMENTO_POR_USUARIO`
- `CREAR_DOCUMENTO_COTIZACION_POR_USUARIO`
- `CAMBIAR_DOCUMENTO_POR_CAJA`
- `ASIGNAR_ABONO_DOCUMENTOS_CREDITO`
- `ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR`
- `GENERAR_DOCUMENTOS_DEVOLUCION`
- `GENERAR_DOCUMENTOS_NOTA_DEBITO`
