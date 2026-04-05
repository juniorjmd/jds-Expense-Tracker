# Revision Back Front: DatosIniciales

## Objetivo

Cerrar `DatosIniciales` con cobertura completa del legacy visible y adaptar el frontend solo donde hay consumo confirmado.

## Acciones backend cubiertas

- `GET_SUCURSAL_PRINCIPAL_DATA`
- `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
- `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
- `23929870008e23007350be74a708ab3a806dce13`
- `8e9ae038c37d3b59fc1eed456c77aefb5eadffea`
- `99c505a66a9d8a984059baf1b99bb9e6456ae4bb`

## Uso real en frontend

### Confirmado

- `GET_SUCURSAL_PRINCIPAL_DATA`
  - `src/app/services/DatosIniciales.services.ts`
  - `src/app/modules/login/pages/login/login.component.ts`
  - `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
  - otros consumidores indirectos del observable de sucursal

### Sin uso actual confirmado

- `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
- `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
- `23929870008e23007350be74a708ab3a806dce13`
- `8e9ae038c37d3b59fc1eed456c77aefb5eadffea`
- `99c505a66a9d8a984059baf1b99bb9e6456ae4bb`

## Contrato backend

El modulo ahora responde siempre con:

- `ok`
- `data`
- `error`

Para sucursal principal:

- `data.branches`
- `data.count`

## Adaptacion frontend

`DatosInicialesService` desempaqueta `response.data.branches` y sigue exponiendo un arreglo de `vwsucursal[]` para no romper consumidores existentes.

## Tests

### Backend

- `tests/Unit/DatosInicialesParametersTest.php`
- `tests/Unit/DatosInicialesServiceTest.php`

### Frontend

- `src/app/services/DatosIniciales.services.spec.ts`

## PRs esperados

- backend: PR del modulo `DatosIniciales` en `jds-backend-app-2026`
- frontend: PR de alineacion `DatosIniciales` en `https://github.com/juniorjmd/jds-frontend-2026.git`

## Validacion real local

Fecha de validacion: `2026-04-04`

Entorno:

- frontend local: `http://localhost/jds_carwash/`
- backend local: `http://localhost/jds_back_2026/api/`
- base de datos real conectada desde `.env`

Prueba ejecutada:

- `POST http://localhost/jds_back_2026/api/datosiniciales/`
- body:
  - `action: GET_SUCURSAL_PRINCIPAL_DATA`

Resultado:

- `ok: true`
- se devolvio `data.branches`
- el login del frontend ya pudo cargar datos iniciales reales contra backend nuevo
