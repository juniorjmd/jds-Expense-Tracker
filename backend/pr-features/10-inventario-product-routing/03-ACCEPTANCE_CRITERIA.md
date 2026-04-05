# Feature-10: Inventario Product Routing - ACCEPTANCE CRITERIA

## Criterios Funcionales
- Las acciones nuevas quedan registradas en `Routes::map()`.
- `InventarioController` expone los métodos nuevos de catálogo/productos.
- `InventarioService` devuelve estructuras compatibles con el frontend actual para búsquedas, precargue y edición básica.

## Criterios de No-Regresión
- Las 4 acciones legacy ya migradas de `inventario` siguen resolviendo.
- `inventario/` responde en formato raíz legacy para mantener compatibilidad con componentes existentes.

## Tests
- `tests/Unit/InventarioParametersTest.php`
- `tests/Unit/InventarioServiceTest.php`

## Condición de Completitud
- El flujo real de productos contra `/inventario/` queda cubierto.
- Lo no encontrado en consumo de UI queda documentado como fuera de alcance.
