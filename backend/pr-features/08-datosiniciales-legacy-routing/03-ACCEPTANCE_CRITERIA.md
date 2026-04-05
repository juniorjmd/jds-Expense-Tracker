# Feature-08: DatosIniciales Module Legacy Routing - ACCEPTANCE CRITERIA

## Criterios funcionales

- las seis acciones visibles del legacy quedan registradas en `Routes::map()`
- `DatosInicialesController` expone handlers para cada accion visible
- `GET_SUCURSAL_PRINCIPAL_DATA` responde ramas en `data.branches`
- las acciones de cambio de contraseña validan confirmacion
- la asignacion de preguntas valida `componente`, `formulario` y `preguntas`

## Criterios de no regresion

- el modulo responde con envelope estandar `ok/data/error`
- la suite backend completa sigue pasando

## Validacion ejecutada

- `php tests/Unit/DatosInicialesParametersTest.php`
- `php tests/Unit/DatosInicialesServiceTest.php`
- `php tests/run-tests.php`
