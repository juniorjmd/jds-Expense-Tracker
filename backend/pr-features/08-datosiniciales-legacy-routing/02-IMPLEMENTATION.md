# Feature-08: DatosIniciales Module Legacy Routing - IMPLEMENTATION

## Cambios realizados

- `config/datosiniciales-actions.php` ahora registra todas las acciones visibles del legacy
- `DatosInicialesController.php` deja de usar respuesta legacy directa y pasa a `Response::ok()` / `Response::fail()`
- `DatosInicialesService.php` ahora devuelve payloads estandar para:
  - sucursal principal
  - cambio de contraseña por session
  - cambio de contraseña por codigo de usuario
  - resultados de simulacro
  - generacion de PDF de simulacro
  - asignacion de preguntas a formulario
- `Router.php` ahora inyecta `Request` en `DatosInicialesController`
- se ampliaron pruebas de parametros y servicio

## Nota importante

Solo `GET_SUCURSAL_PRINCIPAL_DATA` tiene uso confirmado en el frontend actual.

Las otras acciones quedaron migradas por cobertura legacy, pero con logica transitoria o simulada mientras se confirma si siguen teniendo relevancia funcional real fuera del frontend actual.
