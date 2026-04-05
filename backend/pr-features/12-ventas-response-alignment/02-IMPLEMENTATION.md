# Implementacion

## Cambios realizados

- `VentasController` dejo de usar `Response::ok()` y `Response::fail()` para evitar el wrapper `{ ok, data, error }`.
- Las respuestas exitosas ahora salen como payload legacy plano.
- `VentasService` ahora construye respuestas con:
  - `error: ok`
  - `numdata: 1`
  - `data.documentoFinal`
- El `documentoFinal` incluye el `orden`, los pagos enviados y un `resumen` con la operacion calculada.

## Motivacion

El frontend en `Cajas.services.ts`, `generar-cnt-por-cobrar.component.ts` y `generar-cnt-por-pagar.component.ts` lee `datos.data.documentoFinal`, por lo que el wrapper moderno rompia esos flujos.
