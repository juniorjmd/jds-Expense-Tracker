# Expense Tracker SaaS

Plataforma web para controlar ingresos, gastos y operaciones por establecimiento desde una sola cuenta.

Este proyecto esta pensado como la base de un SaaS, es decir, una aplicacion que pueda crecer para atender multiples negocios, sucursales y usuarios con distintos permisos desde una misma plataforma.

Version publicada actual: `1.0.0.1`

## Que puede hacer hoy

- iniciar sesion con usuarios del sistema
- administrar establecimientos
- registrar ingresos y gastos por establecimiento
- crear gastos predeterminados para agilizar capturas repetitivas
- consultar resumen mensual consolidado
- administrar usuarios y permisos

## Como se usa

En ambiente local, la aplicacion esta publicada asi:

- frontend: `http://localhost/expense-tracker/`
- backend API: `http://localhost/expense-tracker-back/api/`

## Estructura general

- `frontend/`: interfaz web de la plataforma
- `backend/`: API, logica de negocio y acceso a base de datos
- `pr-features/`: paquete global de PR con specs, implementacion y criterios de aceptacion del avance actual

## Vision SaaS

La intencion del producto no es quedarse como una app cerrada de un solo negocio. La base actual ya apunta a una evolucion SaaS con estos principios:

- multiples establecimientos por cuenta
- usuarios con roles y permisos
- separacion clara entre frontend, backend y base de datos
- posibilidad de agregar mas modulos sin rehacer la plataforma
- camino abierto para multiempresa o multi tenant en una siguiente etapa

## Siguientes pasos recomendados

- separar formalmente cuentas o tenants
- agregar recuperacion de contrasena y gestion de sesiones
- auditar permisos por modulo
- agregar facturacion, planes o suscripciones
- preparar despliegue cloud con dominio, SSL y backups

## Documentacion tecnica

La parte tecnica detallada vive dentro de cada desarrollo:

- [backend/README.md](c:\desarrollo\back\php\proyecto-jds-Expense-Tracker\backend\README.md)
- [frontend/README.md](c:\desarrollo\back\php\proyecto-jds-Expense-Tracker\frontend\README.md)
