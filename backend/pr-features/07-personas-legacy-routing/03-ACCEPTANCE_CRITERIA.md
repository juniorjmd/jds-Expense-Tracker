# Feature-07: Personas Module Legacy Routing - ACCEPTANCE_CRITERIA

## Criterios Funcionales
- `BUSCAR_ODOO_TITULO_PERSONA` existe en `Routes::map()`
- `GET_MAESTROS_CLIENTES` existe en `Routes::map()`
- `PersonasController` expone ambos métodos
- `PersonasService` rechaza peticiones sin autenticación
- `PersonasService` retorna estructura consistente para ambas acciones

## No-Regresión
- No rompe rutas legacy previas
- No rompe `Routes::map()`

## Tests
- `tests/Unit/PersonasParametersTest.php`
- `tests/Unit/PersonasServiceTest.php`
