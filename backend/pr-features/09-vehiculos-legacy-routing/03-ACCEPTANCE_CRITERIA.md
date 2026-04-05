# Feature-09: Vehiculos Module Legacy Routing - ACCEPTANCE CRITERIA

## Criterios Funcionales
- `CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO` queda registrada en `Routes::map()`.
- `VehiculosController` expone `createDocumentForVehicleService()`.
- `VehiculosService` acepta `_arraydatos` y devuelve `error` e `idDocumento`.
- La acción exige autenticación.

## Criterios de No-Regresión
- No se migran endpoints adicionales del módulo sin evidencia de uso.
- El flujo legacy conserva las claves que el frontend actual inspecciona en la respuesta.

## Tests
- `tests/Unit/VehiculosParametersTest.php`
- `tests/Unit/VehiculosServiceTest.php`

## Condición de Completitud
- El único endpoint dedicado de `vehiculos/` usado por el frontend queda migrado.
- El resto del módulo queda documentado como fuera de alcance de esta feature.
