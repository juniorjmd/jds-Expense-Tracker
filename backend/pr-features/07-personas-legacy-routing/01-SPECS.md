# Feature-07: Personas Module Legacy Routing - SPECS

## Objetivo
Implementar compatibilidad backward-compatible para las acciones legacy iniciales del módulo Personas.

## Requisitos Funcionales

### RF-01: Mapeo de acciones legacy
El sistema debe mapear:

| Acción | Método Esperado | Descripción |
|--------|-----------------|-------------|
| `BUSCAR_ODOO_TITULO_PERSONA` | `PersonasController::searchOdooPersonTitle()` | Consulta títulos de persona desde Odoo |
| `GET_MAESTROS_CLIENTES` | `PersonasController::getClientMasters()` | Retorna maestros requeridos para formularios de clientes |

### RF-02: Seguridad
- Ambas acciones requieren usuario autenticado

### RF-03: Compatibilidad
- Las acciones deben resolverse por routing legacy sin romper módulos existentes
- La respuesta debe mantener formato compatible con `Response::ok()` / `Response::fail()`
