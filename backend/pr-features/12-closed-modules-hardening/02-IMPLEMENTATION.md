# Implementacion

- `LegacyCrudController`, `LegacyCrudService` y `LegacyCrudRepository` ahora cubren las acciones raiz faltantes que consumen `Admin`, `Carwash` y `Vehiculos`.
- `BUSCAR_STOCK_LOCATION` ya no falla por ausencia de Odoo: responde desde `vw_inv_bodegas` y cruza asignaciones con `establecimiento`.
- `INSERT_PERFIL_USUARIO` usa el procedimiento real `setPerfilAUsuario`.
- `mnbvcxzxcxcxasdfewq15616` devuelve cajas con bandera `asignada`.
- `qwer12356yhn7ujm8ik` reemplaza relaciones de cajas por usuario dentro de transaccion.
- `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT` soporta `_deleteBefore`, referencias a columnas de la tabla origen y valores literales.
- `Response` permite `X-Session-Token` para que Apache local no rompa autenticacion en estas acciones.
