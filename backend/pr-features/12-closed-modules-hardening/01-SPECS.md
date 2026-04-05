# Closed Modules Hardening

## Objetivo

Cerrar en un solo frente las brechas compartidas que seguían abiertas en los modulos que ya se consideraban revisados:

- `Admin`
- `Carwash`
- `Vehiculos`
- `Inventario`

## Alcance backend

- completar las acciones raiz legacy usadas por esos modulos:
  - `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT`
  - `INSERT_PERFIL_USUARIO`
  - `mnbvcxzxcxcxasdfewq15616`
  - `qwer12356yhn7ujm8ik`
  - `BUSCAR_STOCK_LOCATION`
- mantener un unico envelope de respuesta `ok/data/error`
- usar fallback local para locaciones de bodega mientras no exista integracion real con Odoo en el backend nuevo

## Validacion esperada

- tests backend en verde
- build frontend en verde
- validacion real local de acciones no destructivas
- dejar explicitamente documentadas las acciones destructivas que quedan pendientes de prueba manual controlada
