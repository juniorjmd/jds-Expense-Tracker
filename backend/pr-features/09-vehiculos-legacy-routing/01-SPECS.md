# Feature-09: Vehiculos Module Legacy Routing - SPECS

## Objetivo
Migrar únicamente la acción legacy específica de `vehiculos` que hoy usa el frontend contra el endpoint dedicado del módulo.

## Requisitos Funcionales

### RF-01: Mapeo de acción usada por el frontend
El sistema debe mapear:

| Acción | Método Esperado | Uso detectado en frontend |
|--------|-----------------|---------------------------|
| `CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO` | `VehiculosController::createDocumentForVehicleService()` | `VehiculosService.guardarNuevoIngresoServicio()` |

### RF-02: Compatibilidad de payload
- La acción debe aceptar `_arraydatos` en formato legacy.
- La respuesta debe conservar las claves que el frontend ya valida: `error` e `idDocumento`.

### RF-03: Integración mínima real
- La implementación debe usar autenticación real.
- La implementación debe persistir el ingreso del vehículo y el detalle del documento usando base de datos.

### RF-04: Alcance explícito
- No se migran acciones adicionales del módulo porque no se encontró otro `action` dedicado a `vehiculos/` en el frontend fuente.
