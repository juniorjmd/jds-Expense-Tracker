# Back Front Alignment

## Hallazgo

Los modulos que ya parecian cerrados seguian dependiendo de acciones raiz legacy y no solo de controladores por modulo. Esa fue la razon principal por la que `Admin`, `Carwash`, `Vehiculos` e `Inventario` todavia mostraban brechas al probar con datos reales.

## Ajuste

- Backend nuevo:
  - implementa las acciones raiz faltantes
  - conserva envelope estandar
  - desacopla `BUSCAR_STOCK_LOCATION` de Odoo para entorno local
- Frontend:
  - adapta servicios y componentes que aun leian `error == 'ok'`
  - mantiene la lectura del contrato estandar desde la capa de servicios

## Validacion real local

- login real: ok
- `mnbvcxzxcxcxasdfewq15616`: ok
- `BUSCAR_STOCK_LOCATION`: ok
- `e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473`: ok
- CRUD generico validado sobre `test_crud_clientes`:
  - `DATABASE_GENERIC_CONTRUCT_INSERT`: ok
  - `DATABASE_GENERIC_CONTRUCT_SELECT`: ok
  - `DATABASE_GENERIC_CONTRUCT_UPDATE`: ok
  - `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT`: ok
  - `DATABASE_GENERIC_CONTRUCT_DELETE`: ok

## Pendiente controlado

Por tratarse de acciones destructivas sobre la BD compartida, `INSERT_PERFIL_USUARIO`, `qwer12356yhn7ujm8ik` y `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT` quedan implementadas pero reservadas para validacion manual controlada desde UI.
