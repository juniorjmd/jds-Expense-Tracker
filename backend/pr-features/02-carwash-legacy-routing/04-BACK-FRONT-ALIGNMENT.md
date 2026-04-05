# Carwash Back Front Alignment

## Objetivo

Revisar el modulo `Carwash` ya creado en backend nuevo, contrastarlo contra legacy y dejar identificado el ajuste requerido en frontend para consumir el contrato estandar `ok/data/error`.

## Acciones legacy cubiertas hoy

- `ABRIR_CAJA_ACTIVA`
- `CERRAR_CAJA_ACTIVA`
- `CERRAR_CAJA_PARCIAL`
- `OBTENER_RESUMEN_CAJA`

Archivo de mapeo:

- `config/carwash-actions.php`

## Hallazgos backend

- el modulo nuevo solo cubre acciones de caja
- en legacy estas acciones existen repartidas entre:
  - `services/view/action/index.php`
  - `services/view/action/ventas/index.php`
- la implementacion nueva no estaba lista para reemplazo directo:
  - mezclaba `resolve()` con un metodo `user()` que `AuthContext` real no expone
  - devolvia datos simulados sin envelope funcional para frontend

## Ajuste aplicado en esta pasada

- `CarwashService` ahora valida autenticacion con `AuthContext::resolve()` en todos los metodos
- mantiene respuesta estandar del backend nuevo
- el payload del modulo queda normalizado asi:
  - `openBox` => `message` + `box`
  - `closeBox` => `message` + `summary`
  - `closePartialBox` => `message` + `summary`
  - `getBoxSummary` => `summary`

Nota:

- la logica sigue siendo transitoria y simulada mientras no se conecte al procedimiento legacy real de caja
- el objetivo de esta pasada es alinear contrato y consumo front, no cerrar todavia la persistencia final del modulo

## Consumidores frontend detectados

- `src/app/services/Cajas.services.ts`
- `src/app/modules/pos/pages/abrir-caja/abrir-caja.component.ts`
- `src/app/modules/pos/pages/cerrar-caja/cerrar-caja.component.ts`
- `src/app/modules/pos/modals/definir-base-caja/definir-base-caja.component.ts`

## Diferencia clave frente al frontend legacy

El frontend actual todavia espera respuestas planas tipo:

- `respuesta.error === 'ok'`
- `respuesta.numdata`
- `respuesta.data`
- `respuesta.datos[0].msg`

El modulo nuevo debe consumirse via envelope estandar:

- `response.ok`
- `response.data.message`
- `response.data.box`
- `response.data.summary`
- `response.error.message`

## Cambio frontend requerido

- normalizar `Cajas.services.ts` para desempaquetar `response.data`
- centralizar lectura de errores del envelope estandar
- adaptar modales y paginas de caja para usar `message`, `box` y `summary`
- agregar test del servicio de caja para asegurar compatibilidad con el contrato estandar

## Estado del modulo

- estado: migrado parcial
- contrato backend/frontend: alineado en esta pasada
- reemplazo directo del legacy: pendiente
- brecha principal restante:
  - implementar logica real de caja y procedimientos heredados
  - decidir si estas acciones se quedan en `Carwash` o si parte de su ownership debe moverse a `Ventas`
