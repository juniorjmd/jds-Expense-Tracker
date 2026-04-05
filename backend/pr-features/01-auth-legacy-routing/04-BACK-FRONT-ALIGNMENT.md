# Revision Back Front: Auth

## Objetivo

Revisar el modulo `Auth` ya migrado cruzando:

- acciones legacy soportadas en backend
- contrato real del legacy
- contrato actual del backend nuevo
- consumidores reales del frontend

La meta ya no es copiar el payload legacy, sino dejar un contrato estandar en backend y documentar la adaptacion requerida en frontend.

## Acciones backend cubiertas hoy

Archivo principal:

- `config/actions.php`

Acciones visibles:

- `ef2e1d89937fba9f888516293ab1e19e7ed789a5` -> login
- `16770d92a6a82ee846f7ff23b4c8ad05b69fba03` -> validatekey
- `16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03` -> me
- `RESETEAR_USUARIO_PASS` -> resetpassword
- `HIJODELAGRANCHINGADA` -> setpassword

## Hallazgo principal

El modulo `Auth` ya soporta las acciones legacy y acepta parametros legacy, pero no esta alineado con una respuesta estandar unica entre backend y frontend.

### Legacy real

En `jds-backend-legacy/services/view/action/login/index.php` se observa un patron tipo:

- `error` como string
- `data` con objetos de usuario
- errores HTTP con payload no estandarizado

### Backend nuevo actual

Archivos principales:

- `app/Modules/Auth/AuthController.php`
- `app/Modules/Auth/Services/AuthService.php`
- `app/Core/Http/Response.php`

Patron actual:

- internamente `AuthService` usa `success`, `code`, `message`, `status`, `data`
- el controlador retorna solo `data` en casos exitosos
- `Response::fail()` retorna `ok`, `data`, `error`

Conclusión:

- hoy `Auth` no expone todavia un contrato final unico y consistente para el frontend
- el modulo esta a mitad de camino entre contrato legacy y contrato estandar

## Parametros legacy ya aceptados

### Login

- `_usuario`
- `_password`

### ValidateKey / Me

- `_llaveSession`

### ResetPassword

- `_usuario`

### SetPassword

- `_id_usuario`
- `_pass`

## Consumidores reales del frontend detectados

### Servicio base

- `src/app/services/login.services.ts`

Metodos detectados:

- `getLogin()`
- `getUsuarioLogeado()`
- `getUsuarioLogeadoObs()`
- `getUsuarioLogeadoAsync()`
- `setRenovacionContrasena()`
- `SET_PASS_USUARIO()`
- `getDatosUsuarioLogeado()`

### Componentes y paginas consumidoras

- `src/app/modules/login/pages/login/login.component.ts`
- `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
- `src/app/components/home/home.component.ts`
- `src/app/components/inicio/inicio.component.ts`
- `src/app/components/mi-usuario/mi-usuario.component.ts`
- `src/app/modules/pos/pages/ventas/ventas.component.ts`
- `src/app/modules/compras/pages/crear/crearCompra.component.ts`
- `src/app/modules/compras/pages/editar/editarCompra.component.ts`

## Acoplamientos actuales del frontend al contrato viejo

### Login

En `login.component.ts` se espera:

- `datos.data.usuario`
- `datos.data.usuario.key_registro`
- `datos.data.usuario.change_pass`

### Forgot password

En `forgotPassWord.component.ts` se espera:

- `datos.error === 'ok'`

### Cambio de contraseña

En `mi-usuario.component.ts` se espera:

- `val.error == 'ok'`

### Usuario autenticado

En `home.component.ts` y `mi-usuario.component.ts` se espera:

- `datos.data.usuario`

## Brechas concretas a cerrar

### Backend

- definir el contrato estandar final de `Auth`
- hacer que todas las acciones del modulo respondan con la misma forma
- revisar si los casos de exito deben salir por `Response::ok()` o por un envelope especifico del proyecto
- normalizar mensajes y errores para login, validatekey, me, resetpassword y setpassword

### Frontend

- centralizar lectura del contrato de `Auth` en `login.services.ts`
- evitar que componentes lean payloads ambiguos o dependan de `error == 'ok'`
- unificar manejo de errores sin depender de `e.error.error` heredado

## Propuesta de alineacion del modulo

### Backend

Definir una respuesta estandar consistente para `Auth`, por ejemplo:

- `ok`
- `data`
- `error`

o el formato final que el proyecto decida, pero uno solo para todas las acciones del modulo.

### Frontend

Actualizar primero:

- `src/app/services/login.services.ts`

Y luego adaptar consumidores para que dependan de ese servicio ya normalizado, en lugar de interpretar la respuesta cruda en cada componente.

## Archivos backend candidatos a cambio

- `app/Modules/Auth/AuthController.php`
- `app/Modules/Auth/Services/AuthService.php`
- `app/Core/Http/Response.php`
- `tests/Unit/AuthServiceTest.php`
- `tests/Unit/AuthParametersTest.php`

## Archivos frontend candidatos a cambio

- `src/app/services/login.services.ts`
- `src/app/modules/login/pages/login/login.component.ts`
- `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
- `src/app/components/home/home.component.ts`
- `src/app/components/mi-usuario/mi-usuario.component.ts`

## PRs esperados

### Backend

- PR del modulo `Auth` en `jds-backend-app-2026`

### Frontend

- PR de alineacion `Auth` en `https://github.com/juniorjmd/jds-frontend-2026.git`

## Estado de la revision

- backend: revisado en mapeo, parametros y forma de respuesta
- frontend: consumidores principales identificados
- siguiente paso: implementar contrato estandar del modulo y adaptar `login.services.ts`

## Validacion real local

Fecha de validacion: `2026-04-04`

Entorno:

- frontend local: `http://localhost/jds_carwash/`
- backend local: `http://localhost/jds_back_2026/api/`
- base de datos real conectada desde `.env`

Prueba ejecutada:

- `POST http://localhost/jds_back_2026/api/login/`
- body:
  - `action: ef2e1d89937fba9f888516293ab1e19e7ed789a5`
  - `_usuario: juniorjmd`
  - `_password: Prom2001josdom*`

Resultado:

- `ok: true`
- login exitoso
- se devolvio `usuario.key_registro`
- el backend autentica correctamente con datos reales
