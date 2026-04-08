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

## CORS y dominios

El backend acepta origenes configurables desde `backend/.env` usando:

- `APP_FRONTEND_URL`
- `APP_CORS_ALLOWED_ORIGINS`

En produccion actual, la idea es publicar:

- frontend: `https://expense-tracker.sofdla.net`
- backend: `https://expense-tracker-php.sofdla.net/api`

El backend ya contempla CORS por lista de dominios y soporte para el header `X-User-Id`.

## Consideraciones SaaS

La base actual ya contempla piezas utiles para una evolucion SaaS:

- roles de usuario
- asignacion de usuarios a establecimientos
- separacion de modulos de negocio
- API desacoplada del frontend

Para una siguiente etapa SaaS se recomienda agregar:

- billing real
- feature flags por plan
- sesiones/tokenizacion real
- observabilidad y auditoria extendida
- automatizacion de despliegues por ambiente

## Notas

- el backend hoy funciona publicado en `http://localhost/expense-tracker-back/api/`
- el archivo `schema.sql` deja la base limpia y solo crea el superusuario inicial
- hay restos legacy importados de otro proyecto, pero la API activa del Expense Tracker ya usa su propio flujo
