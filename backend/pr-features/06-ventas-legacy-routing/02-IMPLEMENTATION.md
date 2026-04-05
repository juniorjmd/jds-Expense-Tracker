# Feature-06: Ventas Module Legacy Routing - IMPLEMENTATION

## Arquitectura Implementada

### Estructura de Archivos
```
app/Modules/Ventas/
├── VentasController.php
└── Services/
    └── VentasService.php

config/
└── ventas-actions.php
```

## Componentes Principales

### VentasController
- Recibe `Request` y `VentasService`
- Normaliza `pagos` cuando llega como JSON string
- Expone cuatro handlers legacy de pagos a crédito

### VentasService
- Valida autenticación con `AuthContext`
- Valida `ordenDocumento`
- Resume pagos válidos y retorna estructura simulada estable
- Deja explícito que la integración real con BD queda pendiente

## Cambios Técnicos
- `Routes.php`: carga `config/ventas-actions.php`
- `Router.php`: añade factory `createVentasController()`
- `tests/run-tests.php`: runner portable para Windows

## Flujo de Ejecución
1. Frontend envía acción legacy del módulo Ventas
2. `Routes::map()` carga `ventas-actions.php`
3. `Router` resuelve `VentasController`
4. `VentasController` normaliza parámetros
5. `VentasService` valida auth y pagos
6. Respuesta JSON compatible
