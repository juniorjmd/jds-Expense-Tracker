# Feature-08: DatosIniciales Module Legacy Routing - SPECS

## Objetivo

Cerrar el modulo `DatosIniciales` con cobertura completa del legacy visible en `datosiniciales/index.php`.

## Requisitos funcionales

### RF-01: Cobertura legacy visible

El modulo debe cubrir:

- `GET_SUCURSAL_PRINCIPAL_DATA`
- `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
- `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
- `23929870008e23007350be74a708ab3a806dce13`
- `8e9ae038c37d3b59fc1eed456c77aefb5eadffea`
- `99c505a66a9d8a984059baf1b99bb9e6456ae4bb`

### RF-02: Envelope estandar

Todas las acciones deben responder con:

- `ok`
- `data`
- `error`

### RF-03: Distincion de uso frontend

- `GET_SUCURSAL_PRINCIPAL_DATA` tiene consumo actual confirmado en frontend
- las otras acciones quedan migradas por cobertura legacy, sin uso actual confirmado en `jds-carwash-front`
