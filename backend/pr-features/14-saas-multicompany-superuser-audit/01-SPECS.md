# Specs: SaaS Multiempresa con Ingreso Explicito y Auditoria

## Objetivo
Evolucionar el Expense Tracker hacia una base SaaS multiempresa en una sola base de datos, manteniendo separacion funcional por empresa y establecimiento, y evitando que el superusuario vea detalle operativo ajeno por defecto.

## Contexto
- La aplicacion ya funciona con Angular + PHP y una sola base de datos.
- El siguiente salto es dejar una estructura consistente para SaaS: empresa -> establecimientos -> usuarios -> transacciones.
- El superusuario necesita capacidad de mantenimiento global, pero con ingreso explicito cuando quiera revisar detalle operativo de una empresa distinta.
- Ese ingreso debe quedar auditado.

## Requisitos funcionales

### 1. Multiempresa en una sola base
- Debe existir una tabla `companies`.
- `users`, `establishments`, `expense_templates` y `transactions` deben relacionarse con `company_id`.
- Cada transaccion debe quedar marcada con `company_id` y `establishment_id`.

### 2. Roles y alcance
- `superusuario` puede crear empresas y administradores de empresa.
- `administrador` solo puede ver y administrar recursos de su propia empresa.
- Un admin no debe ver usuarios, establecimientos o transacciones de otra empresa.

### 3. Mantenimiento de empresas
- Debe existir una pantalla/API de empresas exclusiva para superusuario.
- El superusuario debe poder crear una empresa y su usuario administrador inicial.

### 4. Ingreso explicito del superusuario
- El superusuario no debe ver detalle operativo global por defecto desde el dashboard.
- Para ver detalle ajeno debe entrar a una empresa especifica.
- Debe existir una vista de detalle por empresa.

### 5. Auditoria
- Cada ingreso explicito del superusuario al detalle de una empresa debe registrarse.
- El registro debe guardar al menos: usuario actor, empresa, accion, nota y fecha.

### 6. Frontend Angular
- Debe existir routing Angular para dashboard, empresas, detalle de empresa, usuarios, resumen y establecimiento.
- El dashboard del superusuario debe mostrar una vista de control, no el detalle operativo consolidado.
- La UI debe mantener un lenguaje visual consistente entre tarjetas, paneles y encabezados.

## Requisitos no funcionales
- Mantener compatibilidad con despliegue local en WAMP.
- Mantener `ng build` funcionando.
- Mantener separacion backend/frontend clara.
- Documentar el feature como base de PR.

## Fuera de alcance de este PR
- Facturacion SaaS y planes de suscripcion.
- Branding por tenant.
- Paginacion avanzada y filtros complejos.
- Auditoria completa de todas las acciones criticas.
