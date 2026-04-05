# Feature-02: Carwash Module Legacy Routing - SPECS

## 📋 Objetivo
Implementar compatibilidad backward-compatible para acciones legacy del módulo Carwash, permitiendo que el frontend actual (que usa action-based routing) continúe funcionando tandio que se abre camino para migraciones futuras a `/api/carwash/...`.

## 🎯 Requisitos Funcionales

### RF-01: Mapeo de Acciones Legacy
El sistema debe mapear 4 acciones legacy del módulo Carwash a sus handlers correspondientes:

| Acción | Hash/ID | Método Esperado | Descripción |
|--------|---------|-----------------|-------------|
| Abrir Caja | `ABRIR_CAJA_ACTIVA` | `CarwashController::openBox()` | Permite al usuario abrir una caja para transacciones |
| Cerrar Caja | `CERRAR_CAJA_ACTIVA` | `CarwashController::closeBox()` | Cierra la caja activa y genera resumen |
| Cerrar Caja Parcial | `CERRAR_CAJA_PARCIAL` | `CarwashController::closePartialBox()` | Cierra parcialmente caja (sin cierre definitivo) |
| Resumen Caja | `OBTENER_RESUMEN_CAJA` | `CarwashController::getBoxSummary()` | Obtiene resumen de movimientos de caja |

### RF-02: Parámetros Legacy
El módulo Carwash debe aceptar parámetros siguiendo el mismo patrón de Auth:

**Parámetros esperados por acción:**

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

**Parámetros modernos equivalentes (futuro):**

```json
{
  "usuario": "admin",
  "password": "admin123",
  "token": "token123",
  "motivation": "Apertura de jornada",
  "initial_amount": 500000
}
```

**Prioridad de parámetros**: Parámetro moderno > Parámetro legacy (fallback automático)

### RF-03: Routing Dual
El Router debe seguir soportando:

1. **Ruta Legacy**: POST `/` con `action` en body → `dispatchLegacyAction()`
2. **Ruta Moderna**: POST `/api/carwash/{method}` → `dispatchApiMethod()`

Ambas rutas deben terminar en los mismos métodos de `CarwashService`.

### RF-04: Estructura de Respuesta
Las respuestas del servidor deben mantener compatibilidad con el formato esperado por el frontend:

**Respuesta Exitosa (Abrir Caja):**
```json
{
  "success": true,
  "message": "Caja abierta exitosamente",
  "data": {
    "caja_id": 123,
    "estado": "ABIERTA",
    "usuario": "admin",
    "fecha_hora_apertura": "2026-04-03 09:30:00",
    "monto_inicial": 500000
  }
}
```

**Respuesta Exitosa (Resumen Caja):**
```json
{
  "success": true,
  "data": {
    "total_entrada": 2500000,
    "total_salida": 1200000,
    "total_neto": 1300000,
    "movimientos": [...]
  }
}
```

**Respuesta Errónea:**
```json
{
  "success": false,
  "error": "Caja ya existe abierta para este usuario"
}
```

## ✅ Criterios de Aceptación Principales

1. **Mapeo de Acciones**: Los 4 hashes legacy se resuelven correctamente a sus handlers en Carwash
2. **Parámetros**: Ambos estilos (legacy `_usuario` y moderno `usuario`) funcionan con prioridad correcta
3. **Router**: Detecta ausencia de `/api` y despacha a `dispatchLegacyAction()` correctamente
4. **Unitarios**: Tests confirman que cada acción se mapea y se invoca correctamente
5. **Integración**: Cuando BD esté disponible, tests de integración confirman que el flujo completo funciona

## 📊 Impact Assessment

**Módulos afectados:**
- `app/Bootstrap/Routes.php` - nuevo load de `config/carwash-actions.php`
- `app/Core/Routing/Router.php` - Sin cambios (ya soporta legacy routing)
- `config/carwash-actions.php` - Nuevo archivo

**Módulos NO afectados:**
- Auth (ya implementado)
- Health
- Otros módulos

**Base de datos:**
- Sin cambios
- Reutiliza stored procedures y vistas existentes

**Frontend:**
- SIN CAMBIOS REQUERIDOS
- Continúa usando las mismas acciones legacy

## 🔍 Consideraciones de Seguridad

1. **Validación de token**: Se valida `_llaveSession` o `Authorization` header
2. **Autorización**: Solo usuarios autenticados pueden abrir/cerrar cajas
3. **Auditoría**: Todos los movimientos de caja se registran

## 📝 Notas Técnicas

- CarwashController debe existir en `app/Modules/Carwash/CarwashController.php`
- CarwashService debe existir en `app/Modules/Carwash/Services/CarwashService.php`
- Ambas clases deben tener métodos: `openBox()`, `closeBox()`, `closePartialBox()`, `getBoxSummary()`

## 🎓 Referencias

- [Auth Legacy Routing Implementation](../01-auth-legacy-routing/02-IMPLEMENTATION.md) - Patrón idéntico aplicado a Auth
- [MIGRATION_HANDOFF.md](../../MIGRATION_HANDOFF.md) - Contexto general de migración
- `src/app/models/app.db.actions.ts` - Acciones frontend (líneas ~18-21)
