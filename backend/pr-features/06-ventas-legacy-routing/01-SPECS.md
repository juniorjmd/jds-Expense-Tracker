# Feature-06: Ventas Module Legacy Routing - SPECS

## Objetivo
Implementar compatibilidad backward-compatible para un primer bloque de acciones legacy del módulo Ventas relacionadas con pagos de documentos a crédito.

## Requisitos Funcionales

### RF-01: Mapeo de acciones legacy
El sistema debe mapear estas acciones:

| Acción | Método Esperado | Descripción |
|--------|-----------------|-------------|
| `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO` | `VentasController::assignPurchaseCreditPayments()` | Registra pagos de compras a crédito |
| `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION` | `VentasController::updatePurchaseCreditPayments()` | Actualiza pagos de compras a crédito |
| `ASIGNAR_PAGOS_DOCUMENTOS_CREDITO` | `VentasController::assignSalesCreditPayments()` | Registra pagos de ventas a crédito |
| `ASIGNAR_ABONO_DOCUMENTOS_CREDITO` | `VentasController::assignCreditInstallmentPayment()` | Registra abonos a documentos a crédito |

### RF-02: Parámetros legacy
El módulo debe aceptar parámetros legacy con prefijo `_`:

```json
{
  "action": "ASIGNAR_PAGOS_DOCUMENTOS_CREDITO",
  "_ordenDocumento": 123,
  "_pagos": [
    {
      "idMedioDePago": 1,
      "valorPagado": 50000
    }
  ],
  "_numCuotas": 2,
  "_numDiasCuotas": 30
}
```

## Requisitos de Seguridad
- Todas las acciones requieren usuario autenticado
- No procesar órdenes de documento inválidas
- No aceptar listas de pagos vacías

## Requisitos No Funcionales
- Respuesta JSON consistente con `Response::ok()` y `Response::fail()`
- Configuración de acciones legacy en archivo separado
- Implementación inicial con datos simulados y TODO explícito para integración con BD
