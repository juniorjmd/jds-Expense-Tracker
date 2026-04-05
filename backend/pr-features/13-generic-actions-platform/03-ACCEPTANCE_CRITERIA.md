# Acceptance Criteria

- Todas las acciones `DATABASE_GENERIC_*` y auxiliares compartidos responden con `ok/data/error`.
- Ningun consumidor frontend activo depende de wrappers legacy reconstruidos dentro de servicios compartidos.
- Las pruebas backend del frente generico pasan.
- La documentacion del PR deja trazabilidad de:
  - servicios frontend migrados
  - UIs consumidoras migradas
  - validaciones reales ejecutadas
