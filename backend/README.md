# JDS Car Wash Backend V2

Nuevo backend modular de **JDS Car Wash POS + Inventario**, creado para reemplazar progresivamente el backend legacy actual sin romper el frontend existente.

## Objetivo

Este repositorio contiene la nueva arquitectura del backend, orientada a:

- migrar gradualmente la lógica del sistema legacy
- mantener compatibilidad funcional mientras dura la transición
- separar responsabilidades por módulos
- mejorar mantenibilidad, seguridad y escalabilidad

## Estado del proyecto

Actualmente el proyecto ya cuenta con una base funcional:

- Composer + autoload PSR-4
- `public/index.php` como front controller
- `Request`
- `Response`
- `Router`
- `.env` + `Connection`
- `BaseRepository`
- `QueryBuilder`
- soporte base para expresiones y subqueries
- módulo `Auth` parcialmente migrado
  - `login` funcionando
  - `validatekey` funcionando

## Arquitectura

El proyecto sigue esta estructura:

```text
HTTP
→ Router
→ Controller
→ Service
→ Repository
→ Database
