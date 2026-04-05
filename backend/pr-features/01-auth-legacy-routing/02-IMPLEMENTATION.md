# Implementación: Auth Legacy Routing Support

## Archivos modificados/creados

### 1. `config/actions.php` (CREADO)

Mapea las acciones legacy a handlers en el AuthController.

```php
<?php
declare(strict_types=1);

use App\Core\Http\Request;
use App\Modules\Auth\AuthController;

return [
    // Auth Module Actions
    'ef2e1d89937fba9f888516293ab1e19e7ed789a5' => function (Request $req) {
        $controller = new AuthController();
        return $controller->login($req);
    },
    
    '16770d92a6a82ee846f7ff23b4c8ad05b69fba03' => function (Request $req) {
        $controller = new AuthController();
        return $controller->validatekey($req);
    },
    
    '16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03' => function (Request $req) {
        $controller = new AuthController();
        return $controller->me($req);
    },
    
    'RESETEAR_USUARIO_PASS' => function (Request $req) {
        $controller = new AuthController();
        return $controller->resetpassword($req);
    },
    
    'HIJODELAGRANCHINGADA' => function (Request $req) {
        $controller = new AuthController();
        return $controller->setpassword($req);
    },
];
```

**Propósito**: Proporciona el mapa de acciones legacy que el router usará para enrutar peticiones.

---

### 2. `app/Bootstrap/Routes.php` (MODIFICADO)

Carga las acciones legacy y las combina con las acciones internas.

**Cambio**:
```php
public static function map(): array
{
    $legacyActions = require __DIR__ . '/../../config/actions.php';

    return array_merge([
        'PING' => function (Request $req) {
            return [
                'pong' => true,
                'action' => $req->action(),
                'time' => date('c'),
            ];
        },
    ], $legacyActions ?? []);
}
```

**Propósito**: Integrar las acciones legacy en el mapa global de acciones que usa el Router.

---

### 3. `app/Core/Routing/Router.php` (MODIFICADO - Refactorizado)

**Cambios principales**:

#### 3.1 Método `dispatch()` - Ahora soporta rutas sin `/api`

```php
public function dispatch(Request $request): mixed
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (!$path) {
        Response::fail('INVALID_ROUTE', 'Ruta inválida', 404);
    }

    $path = strtolower($path);
    $segments = explode('/', trim($path, '/'));
    
    $apiIndex = array_search('api', $segments);

    // Si no hay /api, ir directo a Legacy Action Router
    if ($apiIndex === false) {
        return $this->dispatchLegacyAction($request);
    }

    // ... resto del código para /api endpoints ...

    // Si llega aquí, ir a legacy action router
    return $this->dispatchLegacyAction($request);
}
```

**Cambio clave**: El router ahora permite que las peticiones SIN `/api` vayan directo al enrutador legacy.

#### 3.2 Nuevo método `dispatchLegacyAction()`

```php
private function dispatchLegacyAction(Request $request): mixed
{
    $action = $request->action();

    if (!$action) {
        Response::fail(
            'MISSING_ACTION',
            'No se envió action',
            400
        );
    }

    $handler = $this->actionMap[$action] ?? null;

    if (!$handler) {
        Response::fail(
            'ACTION_NOT_FOUND',
            "Acción no registrada: {$action}",
            404
        );
    }

    return $handler($request);
}
```

**Propósito**: Separar la lógica de enrutamiento legacy para mayor claridad.

---

### 4. `app/Modules/Auth/Services/AuthService.php` (MODIFICADO)

Método `login()` - Ahora acepta parámetros con o sin subrayado:

```php
public function login(Request $request): array
{
    // Aceptar tanto 'usuario' como '_usuario' para compatibilidad con frontend legacy
    $usuario = trim((string) $request->input('usuario', $request->input('_usuario', '')));
    // Aceptar tanto 'password' como '_password' para compatibilidad con frontend legacy
    $password = (string) $request->input('password', $request->input('_password', ''));

    if ($usuario === '' || $password === '') {
        return [
            'success' => false,
            'code' => 'VALIDATION_ERROR',
            'message' => 'Debe enviar usuario y password',
            'status' => 422,
        ];
    }

    // ... resto del código sin cambios ...
}
```

**Pattern**: `$request->input('key_nuevo', $request->input('key_legacy', ''))`

Este pattern permite fallback automático a parámetros legacy.

**Cambios en otros métodos**:
- `resetPassword()`: ya soportaba `_usuario`
- `setPassword()`: ya acepta `_id_usuario`, `_pass`
- `validateKey()` y `me()`: no necesitan cambios (usan `_llaveSession` vía `AuthContext`)

---

### 5. `app/Core/Http/Request.php` (SIN CAMBIOS, pero referencia)

El método `action()` ya existía y detecta `action` en body o query:

```php
public function action(): ?string
{
    return $this->body['action']
        ?? $this->query['action']
        ?? null;
}
```

El método `input()` ya existía y acepta fallbacks:

```php
public function input(string $key, mixed $default = null): mixed
{
    return $this->body[$key]
        ?? $this->query[$key]
        ?? $default;
}
```

**Ventaja**: Estos métodos ya soportan el pattern de fallback que usamos.

---

### 6. Archivos de utilidad/testing creados

#### `test-login-legacy.php`
Script de prueba que valida:
- Detección de parámetros legacy
- Acciones mapeadas correctamente
- Compatibilidad del AuthService

**Ejecución**: `php test-login-legacy.php`

#### `router.php`
Router para PHP built-in server que redirige todas las peticiones a `public/index.php`.

**Uso**: `php -S localhost:8000 router.php`

---

## Flujo de ejecución

### Para petición legacy (sin `/api`):

```
POST /
Body: {
  "action": "ef2e1d89937fba9f888516293ab1e19e7ed789a5",
  "_usuario": "admin",
  "_password": "admin123"
}

↓

Router::dispatch()
  ├─ parse_url() → NO hay '/api'
  ├─ dispatchLegacyAction()
  │  ├─ $request->action() → "ef2e1d89936..."
  │  ├─ $actionMap["ef2e1d89936..."] → function(Request)
  │  └─ return $handler($request)
  │
  └─ AuthController::login($request)
     └─ AuthService::login($request)
        ├─ $request->input('usuario', $request->input('_usuario', ''))
        │  → Obtiene "_usuario" del body
        ├─ $repository->login(...)
        └─ return [success => true, data => [...]]
```

### Para petición API moderna (con `/api`):

```
POST /api/auth/login
Body: {
  "usuario": "admin",
  "password": "admin123"
}

↓

Router::dispatch()
  ├─ parse_url() → '/api' ENCONTRADO
  ├─ segments = ['api', 'auth', 'login']
  ├─ AuthController::login($request)
  └─ Same as legacy (mismo código en AuthService)
```

---

## Compatibilidad de parámetros

| Campo | API REST | Legacy | Detecta |
|-------|----------|--------|---------|
| Usuario | `usuario` | `_usuario` | `input('usuario', input('_usuario', ''))` |
| Password | `password` | `_password` | `input('password', input('_password', ''))` |
| Usuario ID | `id_usuario` | `_id_usuario` | `input('id_usuario', input('_id_usuario', 0))` |
| Password new | `pass` | `_pass` | `input('pass', input('_pass', ''))` |
| Token | `key_registro` | `_llaveSession` | `AuthContext::resolveToken()` |

---

## Ventajas de esta implementación

1. **Centralización**: Toda la lógica de negocio está en `AuthService`
2. **Sin duplicación**: No hay dos métodos de login, uno por cada camino
3. **Fácil migración**: El frontend puede migrar poco a poco a `/api`
4. **Mantenibilidad**: Cambios en la lógica se aplican a ambos caminos
5. **Testeable**: Se puede probar cada componente independientemente
