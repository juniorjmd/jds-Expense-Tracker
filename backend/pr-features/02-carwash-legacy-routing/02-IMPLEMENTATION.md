# Feature-02: Carwash Module Legacy Routing - IMPLEMENTATION

## 🏗️ Arquitectura Implementada

### Diagrama de Flujo

```
Frontend (Legacy)
  ↓
POST / {action: 'ABRIR_CAJA_ACTIVA', _usuario: 'admin', _password: '...', ...}
  ↓
Router::dispatch()
  ├─ Detecta: NO /api en URL
  ├─ Llama: dispatchLegacyAction()
  │
  ├─ Busca acción en Routes::map() (desde config/carwash-actions.php)
  │  └─ Encuentra: 'ABRIR_CAJA_ACTIVA' → [CarwashController::class, 'openBox']
  │
  ├─ Instancia: CarwashController (inyecta Request, CarwashService, etc)
  ├─ Invoca: $controller->openBox($request)
  │
  ├─ CarwashController::openBox()
  │  ├─ Extrae parámetros: _usuario, _password, caja_motivo, caja_monto_inicial
  │  ├─ Llama: CarwashService::openBox()
  │  └─ Retorna: Response JSON con estado

  └─ Response al frontend (mismo formato que legacy)

```

## 📂 Cambios de Código

### 1. NUEVO: config/carwash-actions.php

**Responsabilidad**: Mapear 4 acciones legacy de Carwash a handlers

**Ubicación**: `jds-backend-app-2026/config/carwash-actions.php`

```php
<?php
// config/carwash-actions.php

/**
 * Mapea acciones legacy del módulo Carwash
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * 
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 */

use App\Modules\Carwash\CarwashController;

return [
    'ABRIR_CAJA_ACTIVA' => [CarwashController::class, 'openBox'],
    'CERRAR_CAJA_ACTIVA' => [CarwashController::class, 'closeBox'],
    'CERRAR_CAJA_PARCIAL' => [CarwashController::class, 'closePartialBox'],
    'OBTENER_RESUMEN_CAJA' => [CarwashController::class, 'getBoxSummary'],
];
```

**Notas**:
- Retorna array de acciones (exacto patrón de Auth)
- Cada valor es [Clase::class, 'método'] - callable estándar PHP
- Se carga en `Routes.php` via `require_once` y se merge con otras acciones

### 2. ACTUALIZADO: app/Bootstrap/Routes.php

**Cambios**: Cargar `config/carwash-actions.php` además de `config/actions.php`

**Ubicación**: `jds-backend-app-2026/app/Bootstrap/Routes.php`

```php
<?php

namespace App\Bootstrap;

/**
 * Agregador central de acciones (legacy + internas)
 */
class Routes
{
    private static $actionMap = [];

    /**
     * Carga todas las acciones: legacy Auth, legacy Carwash, internas
     */
    public static function map(): array
    {
        if (!empty(self::$actionMap)) {
            return self::$actionMap;
        }

        // Cargar acciones legacy Auth
        $authActions = require_once __DIR__ . '/../../config/actions.php';

        // Cargar acciones legacy Carwash
        $carwashActions = require_once __DIR__ . '/../../config/carwash-actions.php';

        // Agregar acciones internas (si las hay)
        $internalActions = [
            // Ejemplo: 'INTERNAL_ACTION' => [MyController::class, 'method']
        ];

        // Merge todo
        self::$actionMap = array_merge(
            $authActions ?? [],
            $carwashActions ?? [],
            $internalActions
        );

        return self::$actionMap;
    }
}
```

**Cambios exactos**:
- Agregar línea: `$carwashActions = require_once __DIR__ . '/../../config/carwash-actions.php';`
- Agregar al array_merge: `$carwashActions ?? [],`

### 3. NUEVO: app/Modules/Carwash/CarwashController.php

**Responsabilidad**: HTTP handler para acciones Carwash

**Ubicación**: `jds-backend-app-2026/app/Modules/Carwash/CarwashController.php`

```php
<?php

namespace App\Modules\Carwash;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Carwash\Services\CarwashService;

class CarwashController
{
    private Request $request;
    private CarwashService $service;
    private Response $response;

    public function __construct(
        Request $request,
        CarwashService $service,
        Response $response
    ) {
        $this->request = $request;
        $this->service = $service;
        $this->response = $response;
    }

    /**
     * Abre una caja para transacciones
     * Soporta parámetros: caja_motivo, caja_monto_inicial (legacy)
     */
    public function openBox(): void
    {
        try {
            $result = $this->service->openBox(
                motivo: $this->request->input('caja_motivo', ''),
                initialAmount: (float) $this->request->input('caja_monto_inicial', 0)
            );

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Cierra la caja activa
     */
    public function closeBox(): void
    {
        try {
            $result = $this->service->closeBox();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Cierra parcialmente la caja
     */
    public function closePartialBox(): void
    {
        try {
            $result = $this->service->closePartialBox();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }

    /**
     * Obtiene resumen de la caja
     */
    public function getBoxSummary(): void
    {
        try {
            $result = $this->service->getBoxSummary();

            $this->response
                ->status(200)
                ->json(['success' => true, 'data' => $result])
                ->send();
        } catch (\Exception $e) {
            $this->response
                ->status(400)
                ->json(['success' => false, 'error' => $e->getMessage()])
                ->send();
        }
    }
}
```

### 4. NUEVO: app/Modules/Carwash/Services/CarwashService.php

**Responsabilidad**: Lógica de negocio para Carwash

**Ubicación**: `jds-backend-app-2026/app/Modules/Carwash/Services/CarwashService.php`

```php
<?php

namespace App\Modules\Carwash\Services;

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;

class CarwashService
{
    private Request $request;
    private AuthContext $authContext;

    public function __construct(Request $request, AuthContext $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Abre una caja para transacciones
     * 
     * @param string $motivo Razón de apertura (ej: "Apertura de jornada")
     * @param float $initialAmount Monto inicial en caja
     * @return array Estado de la caja
     * @throws \Exception Si hay error en la apertura
     */
    public function openBox(string $motivo = '', float $initialAmount = 0): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar lógica con BD cuando esté disponible
        // Por ahora retorna dato simulado
        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'],
            'estado' => 'ABIERTA',
            'fecha_hora_apertura' => date('Y-m-d H:i:s'),
            'monto_inicial' => $initialAmount,
            'motivo' => $motivo
        ];
    }

    /**
     * Cierra la caja activa
     */
    public function closeBox(): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Implementar lógica con BD cuando esté disponible
        return [
            'caja_id' => 1,
            'estado' => 'CERRADA',
            'fecha_hora_cierre' => date('Y-m-d H:i:s'),
            'total_entrada' => 2500000,
            'total_salida' => 1200000,
            'saldo' => 1300000
        ];
    }

    /**
     * Cierra parcialmente la caja
     */
    public function closePartialBox(): array
    {
        return [
            'caja_id' => 1,
            'estado' => 'PARCIALMENTE_CERRADA',
            'fecha_hora_cierre' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtiene resumen de la caja
     */
    public function getBoxSummary(): array
    {
        $usuario = $this->authContext->user();
        
        if (!$usuario) {
            throw new \Exception('Usuario no autenticado');
        }

        // TODO: Query a BD para obtener movimientos reales
        return [
            'caja_id' => 1,
            'usuario' => $usuario['USUARIO'],
            'estado' => 'ABIERTA',
            'total_entrada' => 2500000,
            'total_salida' => 1200000,
            'total_neto' => 1300000,
            'movimientos_count' => 15,
            'fecha_hora_apertura' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ];
    }
}
```

## 🔄 Flujo Completo Paso a Paso

1. **Frontend envía request**:
   ```
   POST / HTTP/1.1
   Content-Type: application/json
   
   {
     "action": "ABRIR_CAJA_ACTIVA",
     "_usuario": "admin",
     "_password": "***",
     "_llaveSession": "token123",
     "caja_motivo": "Apertura",
     "caja_monto_inicial": 500000
   }
   ```

2. **Router recibe y procesa**:
   - `public/index.php` → carga aplicación
   - `App.php::bootstrap()` → instancia Router
   - `Router::dispatch()` → descompone URL
   - Detecta: NO hay `/api` en URL
   - Llama: `dispatchLegacyAction()`

3. **Router busca en actionMap**:
   - `Routes::map()` retorna todas las acciones (Auth + Carwash + internas)
   - Encuentra: `'ABRIR_CAJA_ACTIVA' => [CarwashController::class, 'openBox']`
   - Instancia: `new CarwashController($request, $carwashService, $response)`

4. **Ejecuta método**:
   - `CarwashController::openBox($request)`
   - Extrae parámetros del request
   - Llama: `CarwashService::openBox()`
   - Retorna resultado

5. **Response al frontend**:
   ```json
   HTTP/1.1 200 OK
   {
     "success": true,
     "data": {
       "caja_id": 1,
       "usuario": "admin",
       "estado": "ABIERTA",
       "fecha_hora_apertura": "2026-04-03 09:30:00",
       "monto_inicial": 500000
     }
   }
   ```

## 🔒 Consideraciones de Implementación

### Autenticación
- El `AuthContext` valida que el usuario esté logueado
- Se obtiene del token: `key_registro` (legacy) o `Authorization` (moderno)
- `AuthService::validateToken()` ya existe y funciona

### Inyección de Dependencias
- Router instancia automáticamente: `new CarwashController($request, $carwashService, $response)`
- Usa el tipo hint del constructor para DI automático
- Si falta `CarwashService`, el Router lanza excepción clara

### Manejo de Errores
- Try-catch en controller captura excepciones de service
- Response 400 con mensaje de error en JSON
- Frontend recibe estructura consistente

### Parámetros Legacy
- Todos usan prefijo `caja_*` para Carwash
- Se extraen con `$request->input('caja_motivo', '')`
- Soportar ambos nombres (legacy y moderno) es para el futuro

## 📊 Impacto Mínimo

- ✅ No afecta Auth (ya existe)
- ✅ No afecta Health
- ✅ No afecta otros módulos
- ✅ No cambia BD
- ✅ No requiere cambios en Frontend
- ✅ Router ya soporta el patrón (no necesita actualización)

## 🎓 Próximos Pasos

1. Crear tests unitarios (verify mapeo de acciones)
2. Crear tests de aceptación (verify flujo completo)
3. Cuando BD esté disponible: tests de integración
4. Replicar patrón a otros módulos si es necesario
