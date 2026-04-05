# Implementacion

- `LegacyCrudController`, `LegacyCrudService` y `LegacyCrudRepository` ya sostienen la cobertura funcional del frente generico compartido.
- El backend expone payloads estandar por tipo de operacion:
  - selects: `data.records`, `data.count`
  - `selectMany`: `data.records` con arreglos por tabla, `data.count`
  - mutaciones: `data.message`, `data.affected`, `data.insertId`, `data.deleted`
  - auxiliares compartidos: payloads dedicados para perfiles, cajas y locaciones
- El endurecimiento de este PR se enfoca en mantener estable ese contrato mientras el frontend migra consumidores.
- Validaciones reales ya registradas para este frente:
  - CRUD controlado sobre `test_crud_clientes`
  - `mnbvcxzxcxcxasdfewq15616`
  - `qwer12356yhn7ujm8ik`
  - `BUSCAR_STOCK_LOCATION`
