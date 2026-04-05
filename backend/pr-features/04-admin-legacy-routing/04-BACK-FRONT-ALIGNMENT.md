# Revision Back Front: Admin

## Objetivo

Cerrar el modulo `Admin` con cobertura real del legacy visible en `administrator/index.php`, manteniendo respuesta estandar del backend y adaptando el frontend para consumir ese contrato desde servicios.

## Acciones legacy cubiertas

- `GET_ALL_RECURSOS`
- `SET_PERFIL_RECURSO`
- `GET_ALL_RECURSOS_BY_PERFIL`
- `CREAR_USUARIO`
- `CREAR_OPERACION_MANUAL`
- `CREAR_OPERACIONES_PREESTABLECIDAS`
- `EJECUTAR_OPERACIONES_PREESTABLECIDAS`

## Acciones no legacy que siguen disponibles

- `OBTENER_USUARIOS`
- `ACTUALIZAR_USUARIO`
- `OBTENER_MENUS`

Estas se mantienen por compatibilidad interna del backend nuevo mientras se confirma si vienen de otro entrypoint legacy o si deben retirarse despues.

## Contrato backend

El backend responde con un solo envelope:

- `ok`
- `data`
- `error`

Cada accion del modulo devuelve su payload dentro de `data`, por ejemplo:

- recursos: `resources`, `count`, `profileId`
- permisos: `selectedResourceIds`, `updatedCount`, `message`
- usuario: `usuarioID`, `message`, `usuario`
- operaciones: `message`, `operationId` u `objeto`

## Consumidores frontend revisados

### Servicios

- `src/app/services/usuario.services.ts`
- `src/app/services/cntContables.service.ts`

### Componentes principales

- `src/app/modules/admin/modules/permisos/pages/perfil/perfil.component.ts`
- `src/app/modules/shared/components/menu-item-li-check/menu-item-li-check.component.ts`
- `src/app/modules/admin/modules/permisos/pages/usuario/nuevo/usuario-nuevo.component.ts`
- `src/app/modules/admin/modules/cuentas-contables/pages/operaciones/pages/crtOperaciones.component.ts`
- `src/app/modules/admin/modules/traslados-cnt/modals/*`

## Hallazgo principal

El frontend estaba acoplado al contrato legacy de `Admin` en dos niveles:

- respuestas exitosas con `error == 'ok'`, `data` y `numdata`
- errores HTTP leidos como `e.error.error`

La alineacion se resolvio centralizando el desempaquetado del envelope estandar en `usuarioService` y `CntContablesService`, y dejando a los componentes solo la lectura de mensajes normalizados.

## Tests

### Backend

- `tests/Unit/AdminParametersTest.php`
- `tests/Unit/AdminServiceTest.php`

### Frontend

- `src/app/services/usuario.services.spec.ts`
- `src/app/services/cntContables.service.spec.ts`

## PRs esperados

### Backend

- PR del modulo `Admin` en `jds-backend-app-2026`

### Frontend

- PR de alineacion `Admin` en `https://github.com/juniorjmd/jds-frontend-2026.git`

## Estado del modulo

- backend: cobertura legacy visible cerrada y respuesta estandar alineada
- frontend: servicios y consumidores principales adaptados
- siguiente paso: continuar con `DatosIniciales` o `Inventario` usando el mismo criterio

## Validacion real local

Fecha de validacion: `2026-04-04`

Entorno disponible:

- frontend local: `http://localhost/jds_carwash/`
- backend local: `http://localhost/jds_back_2026/api/`
- base de datos real conectada desde `.env`

Estado de esta validacion:

- pendiente de ejecutar flujo funcional completo desde UI autenticada
- queda incluido como criterio obligatorio de cierre desde esta fecha
