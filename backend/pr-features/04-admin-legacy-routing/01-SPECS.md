# Feature-04: Admin Module Legacy Routing - SPECS

## Objetivo

Cerrar el modulo `Admin` con cobertura del legacy visible y con respuesta estandar unica en backend.

## Requisitos funcionales

### RF-01: Cobertura legacy visible

El modulo debe cubrir las acciones reales del legacy:

- `GET_ALL_RECURSOS`
- `SET_PERFIL_RECURSO`
- `GET_ALL_RECURSOS_BY_PERFIL`
- `CREAR_USUARIO`
- `CREAR_OPERACION_MANUAL`
- `CREAR_OPERACIONES_PREESTABLECIDAS`
- `EJECUTAR_OPERACIONES_PREESTABLECIDAS`

### RF-02: Parametros legacy aceptados

El modulo debe aceptar los nombres legacy ya usados por frontend:

- `_idPerfil`
- `_perfil`
- `_recursos`
- `_arraydatos`
- `_operacion`

### RF-03: Respuesta estandar

Todas las acciones del modulo deben responder con:

- `ok`
- `data`
- `error`

Sin mezclar contratos legacy a nivel de envelope HTTP.

### RF-04: Adaptacion del frontend

El frontend debe leer el envelope estandar desde servicios y no desde componentes:

- `usuarioService`
- `CntContablesService`

## Requisitos de seguridad

- autenticacion requerida para todas las acciones
- validacion basica de payloads antes de ejecutar operaciones

## Requisitos no funcionales

- tests unitarios en backend para parametros y servicio
- tests de frontend para normalizacion de respuesta
- documentacion del cambio en ambos repos
