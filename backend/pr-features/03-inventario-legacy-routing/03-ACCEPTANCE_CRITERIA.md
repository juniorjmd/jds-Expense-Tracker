# Feature-03: Inventario Legacy Routing - ACCEPTANCE CRITERIA

## Criterios del modulo

### CAT-01

`config/inventario-actions.php` contiene todas las acciones visibles del `inventario/index.php` legacy.

### CAT-02

`InventarioController` ya no devuelve respuestas legacy crudas y usa unicamente:

- `Response::ok()`
- `Response::fail()`

### CAT-03

`InventarioService` devuelve payloads de dominio consistentes dentro de `data`.

### CAT-04

`ProductoService` desempaqueta el envelope estándar y mantiene estable el consumo actual del módulo.

### CAT-05

Las pruebas del módulo pasan:

```bash
php tests/Unit/InventarioParametersTest.php
php tests/Unit/InventarioServiceTest.php
npx ng test jds_carwash --watch=false --browsers ChromeHeadless --include src/app/services/producto.service.spec.ts
```

## Estado esperado

- cobertura legacy visible: completa
- contrato backend: estándar
- frontend: alineado por servicio

