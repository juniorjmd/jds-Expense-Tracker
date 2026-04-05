# Acceptance Criteria

- Las acciones de `ventas/` mantienen su mapping actual.
- Las acciones aceptan parametros legacy con prefijo `_`.
- Las respuestas exitosas incluyen `error`, `numdata` y `data.documentoFinal`.
- Los errores se responden con `error` en raiz para que el frontend pueda mostrarlos.
- Los tests unitarios de `ventas` pasan.
