# Feature-02: Carwash Module Legacy Routing - ACCEPTANCE CRITERIA

## ✅ Criterios de Aceptación

### CAT-01: Mapeo de Acciones Legacy ✅

**Requisito**: Las 4 acciones legacy de Carwash deben estar correctamente mapeadas en `config/carwash-actions.php`

**Evidencia de Cumplimiento**:
- [ ] Archivo `config/carwash-actions.php` existe
- [ ] Contiene 4 acciones:
  - [ ] `'ABRIR_CAJA_ACTIVA' => [CarwashController::class, 'openBox']`
  - [ ] `'CERRAR_CAJA_ACTIVA' => [CarwashController::class, 'closeBox']`
  - [ ] `'CERRAR_CAJA_PARCIAL' => [CarwashController::class, 'closePartialBox']`
  - [ ] `'OBTENER_RESUMEN_CAJA' => [CarwashController::class, 'getBoxSummary']`
- [ ] Archivo retorna un array PHP

**Test Asociado**: `tests/Unit/CarwashParametersTest.php::TEST 1: Carwash actions mapping`

```bash
php tests/Unit/CarwashParametersTest.php
# Esperado output:
# TEST 1: Carwash actions are correctly mapped... ✓ PASSED
```

---

### CAT-02: Actualización de Routes.php ✅

**Requisito**: El archivo `Routes.php` debe cargar `config/carwash-actions.php` y mergearlo

**Evidencia de Cumplimiento**:
- [ ] `app/Bootstrap/Routes.php` contiene: `require_once __DIR__ . '/../../config/carwash-actions.php'`
- [ ] Las acciones de Carwash están en el array final retornado por `Routes::map()`
- [ ] Sin errores de sintaxis PHP

**Test Asociado**: `tests/Unit/CarwashParametersTest.php::TEST 2: Routes loads carwash actions`

```bash
php tests/Unit/CarwashParametersTest.php
# Esperado output:
# TEST 2: Routes loads carwash actions correctly... ✓ PASSED
```

---

### CAT-03: Creación de CarwashController ✅

**Requisito**: Debe existir la clase `CarwashController` con los 4 métodos esperados

**Evidencia de Cumplimiento**:
- [ ] Archivo `app/Modules/Carwash/CarwashController.php` existe
- [ ] Contiene clase `App\Modules\Carwash\CarwashController`
- [ ] La clase tiene los 4 métodos públicos:
  - [ ] `public function openBox(): void`
  - [ ] `public function closeBox(): void`
  - [ ] `public function closePartialBox(): void`
  - [ ] `public function getBoxSummary(): void`
- [ ] Cada método retorna JSON con estructura `['success' => bool, 'data' => array]`

**Test Asociado**: `tests/Unit/CarwashParametersTest.php::TEST 3: CarwashController exists with methods`

```bash
php tests/Unit/CarwashParametersTest.php
# Esperado output:
# TEST 3: CarwashController has required methods... ✓ PASSED
```

---

### CAT-04: Creación de CarwashService ✅

**Requisito**: Debe existir la clase `CarwashService` con los 4 métodos de negocio

**Evidencia de Cumplimiento**:
- [ ] Archivo `app/Modules/Carwash/Services/CarwashService.php` existe
- [ ] Contiene clase `App\Modules\Carwash\Services\CarwashService`
- [ ] La clase tiene los 4 métodos públicos:
  - [ ] `public function openBox(string $motivo = '', float $initialAmount = 0): array`
  - [ ] `public function closeBox(): array`
  - [ ] `public function closePartialBox(): array`
  - [ ] `public function getBoxSummary(): array`
- [ ] Cada método retorna un array con datos de caja

**Test Asociado**: `tests/Unit/CarwashServiceTest.php::TEST 1: CarwashService exists`

```bash
php tests/Unit/CarwashServiceTest.php
# Esperado output:
# TEST 1: CarwashService exists and has methods... ✓ PASSED
```

---

### CAT-05: Parámetros Legacy - openBox() ✅

**Requisito**: El controlador debe aceptar parámetros legacy para abrir caja

**Escenario**: Frontend envía POST con action `ABRIR_CAJA_ACTIVA` y parámetros `caja_motivo`, `caja_monto_inicial`

**Evidencia de Cumplimiento**:
- [ ] CarwashController::openBox() extrae correcto: `caja_motivo` y `caja_monto_inicial`
- [ ] Pasa valores a CarwashService::openBox()
- [ ] Retorna respuesta JSON con `success: true` e información de caja

**Test Asociado**: `tests/Unit/CarwashParametersTest.php::TEST 4: openBox accepts legacy parameters`

```bash
php tests/Unit/CarwashParametersTest.php
# Esperado output:
# TEST 4: openBox accepts legacy parameters... ✓ PASSED
```

Ejemplo de request que debe funcionar:
```json
{
  "action": "ABRIR_CAJA_ACTIVA",
  "_usuario": "admin",
  "_password": "admin123",
  "_llaveSession": "token123",
  "caja_motivo": "Apertura de jornada",
  "caja_monto_inicial": 500000
}
```

---

### CAT-06: Acción Detectada Correctamente ✅

**Requisito**: El Router debe detectar la acción y validarla

**Escenario**: Request con `action: 'ABRIR_CAJA_ACTIVA'` debe ser reconocido

**Evidencia de Cumplimiento**:
- [ ] `Request::action()` retorna correctamente la acción
- [ ] La acción existe en `Routes::map()`
- [ ] El Router puede buscarla sin errores

**Test Asociado**: `tests/Unit/CarwashParametersTest.php::TEST 5: Action is detected`

```bash
php tests/Unit/CarwashParametersTest.php
# Esperado output:
# TEST 5: Action is detected correctly... ✓ PASSED
```

---

### CAT-07: Respuesta Exitosa - Estructura JSON ✅

**Requisito**: Las respuestas exitosas deben tener estructura consistente

**Estructura esperada**:
```json
{
  "success": true,
  "data": {
    "caja_id": <número>,
    "usuario": "<string>",
    "estado": "<ABIERTA|CERRADA|PARCIALMENTE_CERRADA>",
    "fecha_hora_apertura": "<ISO 8601>",
    "monto_inicial": <número>,
    "motivo": "<string>"
  }
}
```

**Evidencia de Cumplimiento**:
- [ ] Response tiene key `success` con value `true`
- [ ] Response tiene key `data` con array de información
- [ ] Estructura matches con SPECS.md § RF-04

**Test Asociado**: `tests/Unit/CarwashServiceTest.php::TEST 2: Response structure`

```bash
php tests/Unit/CarwashServiceTest.php
# Esperado output:
# TEST 2: Response has correct JSON structure... ✓ PASSED
```

---

### CAT-08: Respuesta Errónea - Validación ✅

**Requisito**: Las respuestas de error deben tener estructura consistente

**Escenario**: Cuando usuario no está autenticado

**Estructura esperada**:
```json
{
  "success": false,
  "error": "Usuario no autenticado"
}
```

**Evidencia de Cumplimiento**:
- [ ] Response tiene key `success` con value `false`
- [ ] Response tiene key `error` con mensaje descriptivo
- [ ] HTTP Status Code es 400

**Test Asociado**: `tests/Unit/CarwashServiceTest.php::TEST 3: Error response structure`

```bash
php tests/Unit/CarwashServiceTest.php
# Esperado output:
# TEST 3: Error response has correct structure... ✓ PASSED
```

---

### CAT-09: Routing Dual - Sin /api ✅

**Requisito**: Request sin `/api` debe despachar a legacy action

**Request de prueba**:
```http
POST / HTTP/1.1
Content-Type: application/json
{
  "action": "ABRIR_CAJA_ACTIVA",
  ...
}
```

**Evidencia de Cumplimiento**:
- [ ] Router detecta ausencia de `/api`
- [ ] Router llama `dispatchLegacyAction()`
- [ ] La acción se resuelve correctamente
- [ ] No error 404 o 405

**Test Asociado**: `tests/Integration/CarwashLegacyRoutingTest.php::TEST 1: Legacy routing works` (cuando BD esté disponible)

---

### CAT-10: Routing Dual - Con /api (Futuro) ℹ️

**Requisito**: Request con `/api/carwash/...` debe funcionar (futuro)

**Request de prueba**:
```http
POST /api/carwash/openbox HTTP/1.1
{
  "usuario": "admin",
  "password": "admin123",
  "token": "token123",
  "motivation": "Apertura",
  "initial_amount": 500000
}
```

**Estado**: Documentado pero NO implementado en esta feature. Será implementado cuando se complete Feature-X de API REST completa.

---

### CAT-11: Parámetros Modernos (Futuro) ℹ️

**Requisito**: CarwashService debe aceptar parámetros modernos (futuro)

**Cambios necesarios**:
- [ ] Actualizar `openBox(string $motivo, float $initialAmount)` a usar parámetros modernos
- [ ] Usar fallback pattern similar a Auth: `motivo = input('motivation', input('caja_motivo'))`

**Estado**: Documentado pero NO implementado. Será implementado cuando se migre el frontend.

---

## 🧪 Plan de Testing

### Nivel 1: Unitarios (Ahora)

```bash
cd tests/
php Unit/CarwashParametersTest.php
php Unit/CarwashServiceTest.php
```

Esperado: 8/8 tests pasando ✓

### Nivel 2: Integración (Cuando BD esté disponible)

```bash
php Integration/CarwashLegacyRoutingTest.php
```

Esperado: 3/3 tests pasando ✓

### Nivel 3: HTTP Manual (Cuando BD esté disponible)

```bash
# Probar con curl o Postman
curl -X POST http://localhost/jds-backend-app-2026/public/ \
  -H "Content-Type: application/json" \
  -d '{
    "action": "ABRIR_CAJA_ACTIVA",
    "_usuario": "admin",
    "_password": "admin123",
    "_llaveSession": "key123",
    "caja_motivo": "Apertura",
    "caja_monto_inicial": 500000
  }'
```

Esperado: Response 200 con estructura JSON esperada

### Nivel 4: Frontend Real (Cuando todo esté ready)

- Ejecutar frontend actual
- Abrir, cerrar y resumir cajas
- Verificar que todo sigue funcionando sin cambios

---

## 📋 CheckList Final

**Documentación**:
- [ ] 01-SPECS.md completo
- [ ] 02-IMPLEMENTATION.md completo
- [ ] 03-ACCEPTANCE_CRITERIA.md (este archivo) completo

**Código**:
- [ ] config/carwash-actions.php creado ✅
- [ ] app/Bootstrap/Routes.php actualizado ✅
- [ ] app/Modules/Carwash/CarwashController.php creado ✅
- [ ] app/Modules/Carwash/Services/CarwashService.php creado ✅

**Tests**:
- [ ] tests/Unit/CarwashParametersTest.php creado ✅
- [ ] tests/Unit/CarwashServiceTest.php creado ✅
- [ ] Todos los tests unitarios pasando ✅

**Version Control**:
- [ ] Código commiteado
- [ ] Mensaje de commit descriptivo
- [ ] Linked PR en GitHub (si aplica)

**Frontend**:
- [ ] Sin cambios requeridos
- [ ] Compatible con código existente

---

## 🎓 Conclusión

Esta feature implementa compatibilidad backward-compatible para acciones legacy del módulo Carwash siguiendo exactamente el patrón de Auth, pero aplicado a caja (box).

Una vez verificados todos los criterios anteriores, la feature está **LISTA PARA PRODUCCIÓN**.
