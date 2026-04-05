# Backend

Backend API en PHP para el Expense Tracker, preparado como base funcional de un futuro SaaS.

## Responsabilidad

Este desarrollo se encarga de:

- autenticacion
- usuarios y roles
- establecimientos
- transacciones
- gastos predeterminados
- resumen mensual
- acceso a MySQL mediante PDO

## Stack tecnico

- PHP 8
- Composer
- `vlucas/phpdotenv`
- MySQL
- arquitectura propia con `Request`, `Response`, `Router`, `Service` y `Repository`

## Estructura principal

```text
backend/
├── app/
│   ├── Bootstrap/
│   ├── Controllers/
│   ├── Core/
│   ├── Repositories/
│   └── Services/
├── database/
│   └── schema.sql
├── public/
│   └── index.php
└── scripts/
    └── apply_schema.php
```

## Endpoints principales

- `POST /api/auth/login`
- `GET /api/establishments`
- `POST /api/establishments`
- `GET /api/establishments/{id}/transactions`
- `POST /api/establishments/{id}/transactions`
- `GET /api/establishments/{id}/expense-templates`
- `POST /api/establishments/{id}/expense-templates`
- `POST /api/expense-templates/{id}/apply`
- `GET /api/users`
- `POST /api/users`
- `PUT /api/users/{id}`
- `GET /api/summary`

## Base de datos

La conexion se toma desde `backend/.env`.

El esquema actual crea estas tablas principales:

- `users`
- `establishments`
- `user_establishments`
- `categories`
- `expense_templates`
- `transactions`

Para aplicar el esquema:

```powershell
php backend\scripts\apply_schema.php
```

## Consideraciones SaaS

La base actual ya contempla piezas utiles para una evolucion SaaS:

- roles de usuario
- asignacion de usuarios a establecimientos
- separacion de modulos de negocio
- API desacoplada del frontend

Para una siguiente etapa SaaS se recomienda agregar:

- tabla de `tenants` o `companies`
- relacion de establecimientos por tenant
- aislamiento de datos por tenant
- sesiones/tokenizacion real
- billing y planes

## Notas

- el backend hoy funciona publicado en `http://localhost/expense-tracker-back/api/`
- el archivo `schema.sql` incluye datos semilla para acceso inicial
- hay restos legacy importados de otro proyecto, pero la API activa del Expense Tracker ya usa su propio flujo
