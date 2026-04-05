# Feature-11: Personas Response Alignment - ACCEPTANCE CRITERIA

## Criterios Funcionales
- `searchOdooPersonTitle()` devuelve `error` y `data` en raíz.
- `getClientMasters()` devuelve `error` y `datos` en raíz.

## Criterios de No-Regresión
- Las acciones legacy del módulo `personas` siguen registradas en `Routes::map()`.
- La autenticación sigue siendo obligatoria.

## Tests
- `tests/Unit/PersonasServiceTest.php`

## Condición de Completitud
- El módulo `personas` queda alineado con el contrato que hoy consume el frontend.
