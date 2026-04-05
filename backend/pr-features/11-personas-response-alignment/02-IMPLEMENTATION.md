# Feature-11: Personas Response Alignment - IMPLEMENTATION

## Cambios Realizados
- `PersonasService` ahora devuelve payload legacy plano:
  - `searchOdooPersonTitle()` => `error` + `data`
  - `getClientMasters()` => `error` + `datos`
- `PersonasController` dejó de envolver la salida con `Response::ok()` y ahora envía el payload directamente.

## Archivos Modificados
- `app/Modules/Personas/PersonasController.php`
- `app/Modules/Personas/Services/PersonasService.php`

## Motivo
El frontend consume `value.datos` al llamar `ClientesService.getMaestroClientes()`, por lo que el formato `{ ok, data, error }` no era compatible.
