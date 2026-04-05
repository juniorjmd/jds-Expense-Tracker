# Back Front Alignment

## Estado base

- Backend:
  - la familia `DATABASE_GENERIC_*` y auxiliares compartidos ya esta implementada y validada en backend
  - el contrato oficial es `ok/data/error`
- Frontend:
  - la deuda principal estaba en servicios que seguian transformando el contrato estandar a `numdata/error/query`

## Bloques cerrados en esta pasada

- `usuarioService`
  - `GET_ALL_RECURSOS`
  - `GET_ALL_RECURSOS_BY_PERFIL`
  - `SET_PERFIL_RECURSO`
  - `CREAR_USUARIO`
  - `INSERT_PERFIL_USUARIO`
  - `DATABASE_GENERIC_CONTRUCT_SELECT` sobre `perfiles`
  - `DATABASE_GENERIC_CONTRUCT_SELECT` sobre `vw_usuario`
- `cajasServices`
  - `ABRIR_CAJA_ACTIVA`
  - `OBTENER_RESUMEN_CAJA`
  - `CERRAR_CAJA_ACTIVA`
  - `CERRAR_CAJA_PARCIAL`
  - `DATABASE_GENERIC_CONTRUCT_SELECT_BY_USER_LOGGED` sobre cajas
  - `mnbvcxzxcxcxasdfewq15616`
  - `qwer12356yhn7ujm8ik`
  - `e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473` en `getCajasActivasYparametros`
- `CntContablesService`
  - `CREAR_OPERACION_MANUAL`
  - `CREAR_OPERACIONES_PREESTABLECIDAS`
  - `EJECUTAR_OPERACIONES_PREESTABLECIDAS`
- `ProductoService`
  - `GET_CATEGORIAS`
  - `GET_BODEGAS`

## Consumidores frontend alineados en esta pasada

- `modules/admin/modules/permisos/pages/perfil`
- `modules/admin/modules/permisos/pages/usuario`
- `modules/admin/modules/permisos/pages/usuario/nuevo`
- `modules/admin/modules/permisos/pages/usuario/editar`
- `modules/admin/modules/permisos/pages/usuario/perfil`
- `modules/admin/modules/permisos/pages/usuario/detalle`
- `modules/pos/pages/abrir-caja`
- `modules/pos/pages/cerrar-caja`
- `modules/pos/modals/definir-base-caja`
- `modules/vehiculos/pages/ingreso`
- `modules/admin/modules/cuentas-contables/pages/operaciones/pages/crtOperaciones`
- `modules/admin/modules/traslados-cnt/modals/newTrasladoDesdeCaja`
- `modules/admin/modules/traslados-cnt/modals/newTrasladoDeMuchasAUna`
- `modules/admin/modules/traslados-cnt/modals/newTrasladoDeUnaAMuchos`
- `modules/admin/modules/traslados-cnt/modals/newTrasladoAsignarSaldo`
- `modules/admin/modules/traslados-cnt/modals/ejecutarTrasladoDesdeCaja`
- `modules/admin/modules/traslados-cnt/modals/ejecutarDeUnaAMuchos`
- `modules/admin/modules/traslados-cnt/modals/ejecutarDeMuchaAUna`
- `modules/admin/modules/traslados-cnt/modals/ejecutarAsignacionSaldos`
- `components/home`
- `modules/admin/pages/categorias`
- `modules/admin/pages/bodegas`
- `modules/admin/modals/admin-categorias`

## Validacion real registrada

- `GET_ALL_RECURSOS`:
  - `ok: true`
  - `count: 8`
- `GET_CATEGORIAS`:
  - `ok: true`
  - `count: 2`
- `mnbvcxzxcxcxasdfewq15616`:
  - `ok: true`
  - `count: 2`
- `BUSCAR_STOCK_LOCATION` con `_principal = true`:
  - `ok: true`
  - `count: 2`

## Pendiente del frente generico

- `ProductoService` y sus consumidores siguen siendo la mayor deuda restante.
- `CntContablesService` aun conserva payloads legacy internos en operaciones manuales y traslados.
- Despues de esos dos bloques, faltan servicios menores que usan select/update/delete genericos sin haberse migrado por completo.
