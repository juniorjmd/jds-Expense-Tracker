# Feature 12: Ventas Response Alignment

## Objetivo

Alinear las respuestas del modulo `ventas/` con el contrato legacy que consume el frontend actual en los flujos de cuentas por cobrar y cuentas por pagar.

## Alcance

- Ajustar las cuatro acciones ya migradas en `ventas-actions.php`.
- Responder payload plano legacy en raiz.
- Entregar `error`, `numdata` y `data.documentoFinal`.

## Fuera de alcance

- Migrar acciones nuevas no usadas hoy por el frontend.
- Implementar cierre real de documentos o persistencia completa.
- Corregir otros modulos pendientes como `documentos` o `admin`.
