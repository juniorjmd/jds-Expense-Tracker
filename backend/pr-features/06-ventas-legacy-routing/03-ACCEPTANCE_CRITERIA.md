# Feature-06: Ventas Module Legacy Routing - ACCEPTANCE_CRITERIA

## Criterios Funcionales

### CA-01: Acciones legacy cargadas
- `Routes::map()` incluye las 4 acciones de Ventas
- `config/ventas-actions.php` mapea a `VentasController`

### CA-02: Parámetros legacy soportados
- `Request::input('ordenDocumento')` resuelve `_ordenDocumento`
- `Request::input('numCuotas')` resuelve `_numCuotas`
- `Request::input('numDiasCuotas')` resuelve `_numDiasCuotas`

### CA-03: Validaciones mínimas
- Retorna error si no hay autenticación
- Retorna error si `ordenDocumento <= 0`
- Retorna error si no hay pagos válidos

## Criterios de No-Regresión
- No rompe acciones legacy existentes de Auth, Admin, Carwash, Inventario y Documentos
- `Routes::map()` sigue retornando un array válido

## Tests
- `tests/Unit/VentasParametersTest.php`
- `tests/Unit/VentasServiceTest.php`

## Condición de Completitud
- [ ] Mapeo legacy agregado
- [ ] Controlador y servicio creados
- [ ] Tests del módulo ejecutables
- [ ] Documentación creada
