# Feature-03: Inventario Legacy Routing - IMPLEMENTATION

## Backend

Se completó la cobertura visible del legacy del módulo `inventario`.

Cambios principales:

- `config/inventario-actions.php`
  - ahora incluye todas las acciones visibles del entrypoint legacy
- `app/Modules/Inventario/InventarioController.php`
  - ya no envía payloads legacy crudos
  - responde solo con `Response::ok()` y `Response::fail()`
- `app/Modules/Inventario/Services/InventarioService.php`
  - reorganizado para devolver payloads consistentes dentro de `data`
  - agrega soporte a:
    - traslados entre bodegas
    - catalogos de categorias y bodegas
    - busqueda por categoria
    - busqueda por marca
    - variante `BUSCAR_TODOS_LOS_PRODUCTOS_OLD`

Payloads de dominio principales:

- `movement`
- `transfer`
- `categories`
- `warehouses`
- `items`
- `products`
- `product`
- `productExistence`

## Frontend

Se alineó `ProductoService` para leer el contrato estándar del backend nuevo.

Cambios principales:

- nuevo archivo:
  - `src/app/interfaces/inventario-response.interface.ts`
- `src/app/services/producto.service.ts`
  - desempaqueta el envelope `ok/data/error`
  - agrega `getErrorMessage()`
  - adapta respuestas del backend a la forma que aún consumen los componentes actuales
- también se migraron las lecturas de:
  - `GET_CATEGORIAS`
  - `GET_BODEGAS`

## Pruebas

- backend:
  - `tests/Unit/InventarioParametersTest.php`
  - `tests/Unit/InventarioServiceTest.php`
- frontend:
  - `src/app/services/producto.service.spec.ts`

## Resultado

- backend con respuesta HTTP estándar única
- cobertura visible del legacy cerrada para `inventario`
- frontend alineado desde el servicio sin exigir un refactor masivo de todos los componentes del módulo en esta misma pasada
