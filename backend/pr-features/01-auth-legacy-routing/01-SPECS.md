# Specs: Auth Legacy Routing Support

## Objetivo
Mantener compatibilidad con las acciones legacy del frontend actual mientras se implementa la nueva API REST. El backend debe soportar ambos caminos de autenticación sin romper el flujo actual.

## Contexto
- Frontend actual envía acciones legacy en lugar de usar `/api/...` endpoints  
- Backend nuevo debe soportar ambas formas: acciones legacy Y API REST moderna
- Estrategia: ambos caminos terminan en el mismo `AuthService`

## Requisitos funcionales

### 1. Mapeo de acciones legacy
El backend debe reconocer estas 5 acciones Auth legacy y enrutarlas al controlador correspondiente:

| Acción | Hash/ID | Método |
|--------|---------|--------|
| Login | `ef2e1d89937fba9f888516293ab1e19e7ed789a5` | `POST /` con `action` en body |
| Validar llave | `16770d92a6a82ee846f7ff23b4c8ad05b69fba03` | `POST /` con `action` en body |
| Obtener usuario | `16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03` | `POST /` con `action` en body |
| Reset password | `RESETEAR_USUARIO_PASS` | `POST /` con `action` en body |
| Set password | `HIJODELAGRANCHINGADA` | `POST /` con `action` en body |

### 2. Parámetros legacy aceptados

El backend debe aceptar parámetros con prefijo `_` (como envía el frontend legacy):

**Login**
```json
{
  "action": "ef2e1d89937fba9f888516293ab1e19e7ed789a5",
  "_usuario": "username",
  "_password": "password"
}
```

**Validate Key / Get User**
```json
{
  "action": "16770d92a6a82ee846f7ff23b4c8ad05b69fba03",
  "_llaveSession": "token_string"
}
```

**Reset Password**
```json
{
  "action": "RESETEAR_USUARIO_PASS",
  "_usuario": "username"
}
```

**Set Password**
```json
{
  "action": "HIJODELAGRANCHINGADA",
  "_id_usuario": 123,
  "_pass": "newpassword"
}
```

### 3. Enrutamiento

- Rutas **CON `/api`** → Enrutador moderno (`/api/{module}/{method}`)
- Rutas **SIN `/api`** → Enrutador legacy (busca `action` en body/query)
- Ambas terminan en `AuthService` reutilizando la lógica de negocio

### 4. Compatibilidad de parámetros

El `AuthService` debe aceptar ambas formas:
- `usuario` o `_usuario` (con prioridad a la forma sin subrayar)
- `password` o `_password`
- `_llaveSession` para token legacy
- `_id_usuario` para operaciones que necesitan el ID del usuario

## Requisitos no-funcionales

- **Compatibilidad**: No debe romper el frontend actual
- **Mantenibilidad**: La lógica de negocio debe estar centralizada en `AuthService`
- **Testing**: Validar que los parámetros legacy se detectan correctamente
- **Performance**: Sin impacto notable en tiempo de respuesta

## Reglas de negocio

1. Si envía `/api/auth/login` → procesa como API REST
2. Si envía `{action: "ef2e1d89...", ...}` → procesa como legacy
3. El token retornado debe funcionar con ambos caminos
4. Las respuestas pueden tener estructura diferente pero datos iguales
