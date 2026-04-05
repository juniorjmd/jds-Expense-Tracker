# Feature-04: Admin Module Legacy Routing - ACCEPTANCE_CRITERIA

## Criterios funcionales

- `GET_ALL_RECURSOS` responde recursos dentro de `data.resources`
- `GET_ALL_RECURSOS_BY_PERFIL` responde recursos del perfil y `profileId`
- `SET_PERFIL_RECURSO` valida `_perfil` y `_recursos`
- `CREAR_USUARIO` valida persona, login y email
- `CREAR_OPERACION_MANUAL` valida nombre de operacion
- `CREAR_OPERACIONES_PREESTABLECIDAS` y `EJECUTAR_OPERACIONES_PREESTABLECIDAS` validan cuentas

## Criterios de no regresion

- todas las acciones usan envelope estandar `ok/data/error`
- `Request` sigue aceptando prefijos `_`
- la suite backend completa sigue pasando

## Validacion ejecutada

- `php tests/Unit/AdminParametersTest.php`
- `php tests/Unit/AdminServiceTest.php`
- `php tests/run-tests.php`

## Condicion de cierre del modulo

- cobertura legacy visible del modulo `Admin`: cerrada
- respuesta backend del modulo: estandarizada
- frontend del modulo: pendiente de leer solo desde servicios normalizados
