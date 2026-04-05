# Implementacion: SaaS Multiempresa con Ingreso Explicito y Auditoria

## Alcance implementado
Este feature deja funcionando la base multiempresa, el mantenimiento de empresas para superusuario, la vista de detalle explicita por empresa y la auditoria de ese ingreso. Tambien alinea el frontend Angular para que el superusuario no vea la operacion ajena por defecto y mejora la consistencia visual en las pantallas principales.

## Backend

### Esquema y datos
- `backend/database/schema.sql`
  - agrega `companies`
  - relaciona `users.company_id`
  - relaciona `establishments.company_id`
  - relaciona `expense_templates.company_id`
  - relaciona `transactions.company_id`
  - agrega `company_access_logs` para auditoria de ingreso explicito
  - deja semillas base con `Demo Company` y `Super Usuario`

### Repositorios
- `backend/app/Repositories/CompanyRepository.php`
  - lista empresas con conteos
  - obtiene detalle de empresa con conteos
- `backend/app/Repositories/CompanyAccessLogRepository.php`
  - registra acceso del superusuario
  - consulta historial reciente por empresa
- ajustes en:
  - `backend/app/Repositories/UserRepository.php`
  - `backend/app/Repositories/EstablishmentRepository.php`
  - `backend/app/Repositories/ExpenseTemplateRepository.php`
  - `backend/app/Repositories/TransactionRepository.php`
  - ahora filtran/guardan por `company_id`

### Servicios y controladores
- `backend/app/Services/CurrentUserService.php`
  - resuelve el usuario actor desde `X-User-Id`
- `backend/app/Services/CompanyService.php`
  - lista empresas
  - crea empresa + administrador inicial
  - entrega `overview` de empresa y registra acceso
- `backend/app/Controllers/CompanyController.php`
  - `GET /api/companies`
  - `POST /api/companies`
  - `GET /api/companies/{id}`
- ajustes en:
  - `backend/app/Services/UserService.php`
  - `backend/app/Services/EstablishmentService.php`
  - `backend/app/Services/ExpenseTemplateService.php`
  - `backend/app/Services/TransactionService.php`
  - `backend/app/Controllers/UserController.php`
  - `backend/app/Controllers/EstablishmentController.php`
  - `backend/app/Controllers/ExpenseTemplateController.php`
  - `backend/app/Controllers/TransactionController.php`
  - `backend/app/Controllers/SummaryController.php`

### Regla nueva para superusuario
- `GET /api/summary` ya no expone resumen operativo global al superusuario sin empresa objetivo.
- El camino correcto para revisar operacion ajena es `GET /api/companies/{id}`.
- Ese endpoint devuelve:
  - empresa
  - resumen del mes
  - establecimientos
  - usuarios de la empresa
  - historial de accesos

## Frontend Angular

### Modelos y servicios
- `frontend/src/app/models.ts`
  - agrega `Company`, `CompanyOverview`, `CompanyAccessLog`
- `frontend/src/app/services/storage.service.ts`
  - agrega `getCompanies()`
  - agrega `getCompanyOverview(companyId)`
- `frontend/src/app/services/auth.service.ts`
  - reconoce `superusuario`
  - expone permiso `manage-companies`
- `frontend/src/app/services/api.service.ts`
  - sigue enviando `X-User-Id`

### Rutas y paginas
- `frontend/src/app/app.routes.ts`
  - agrega `/empresas`
  - agrega `/empresas/:id`
- `frontend/src/app/pages/companies-page.component.ts`
  - mantenimiento de empresas
  - ingreso al detalle de una empresa
- `frontend/src/app/pages/company-detail-page.component.ts`
  - detalle de empresa con auditoria visible
- `frontend/src/app/pages/dashboard-page.component.ts`
  - dashboard del superusuario sin detalle operativo ajeno por defecto
- `frontend/src/app/pages/summary-page.component.ts`
  - restringe resumen global para superusuario
- `frontend/src/app/pages/users-page.component.ts`
  - mantiene alcance por empresa y mejoras visuales

### UI / diseño
- `frontend/src/styles.css`
  - paleta nueva sobria tipo SaaS
  - fondos con mas profundidad
- se extendio el lenguaje de tarjetas con ancho controlado
  - panel del superusuario
  - empresas creadas
  - listas del detalle por empresa

## Validaciones realizadas
- `php backend/scripts/apply_schema.php`
- `php -l backend/app/Services/CompanyService.php`
- `php -l backend/app/Controllers/CompanyController.php`
- `php -l backend/app/Repositories/CompanyAccessLogRepository.php`
- `php -l backend/app/Controllers/SummaryController.php`
- `npm run build`
- verificacion HTTP:
  - `GET /api/companies/1` con superusuario devuelve overview y crea log
  - `GET /api/summary?month=2026-04` con superusuario responde `403`

## Riesgos / siguientes pasos
- ampliar auditoria a crear/editar/borrar recursos sensibles
- endurecer pruebas backend/frontend
- cerrar consistencia visual restante en pantallas secundarias
- preparar siguiente capa SaaS: configuracion de tenant, planes y branding
