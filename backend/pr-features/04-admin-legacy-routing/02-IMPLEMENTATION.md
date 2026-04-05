# Feature-04: Admin Module Legacy Routing - IMPLEMENTATION

## Cambios realizados

- `config/admin-actions.php` ahora mapea las siete acciones legacy reales de `administrator/index.php`
- `AdminController.php` agrega handlers para recursos, permisos de perfil y operaciones contables
- `AdminService.php` centraliza autenticacion, arbol base de recursos y payloads estandar
- se mantuvieron `OBTENER_USUARIOS`, `ACTUALIZAR_USUARIO` y `OBTENER_MENUS` como compatibilidad adicional
- se agregaron pruebas nuevas en:
  - `tests/Unit/AdminParametersTest.php`
  - `tests/Unit/AdminServiceTest.php`

## Decision de contrato

El backend no replica el envelope legacy de `error`, `data` y `numdata` como raiz.

La raiz HTTP del modulo queda unificada en:

- `ok`
- `data`
- `error`

Los datos especificos del modulo viven dentro de `data`.

## Nota importante

La logica del servicio sigue siendo transitoria y simulada en varias acciones administrativas. Este cierre cubre routing legacy, validacion de payload y contrato de salida, pero no reemplaza aun procedimientos reales de base de datos o correo del legacy.
