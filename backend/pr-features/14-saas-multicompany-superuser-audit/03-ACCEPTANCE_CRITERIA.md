# Criterios de Aceptacion: SaaS Multiempresa con Ingreso Explicito y Auditoria

## Criterios funcionales

### CA-1: Superusuario puede administrar empresas
**Dado** un usuario con rol `superusuario`  
**Cuando** consume `GET /api/companies`  
**Entonces** obtiene la lista de empresas  
**Y** puede crear una nueva empresa con `POST /api/companies`

### CA-2: Administrador normal no puede administrar empresas
**Dado** un usuario con rol `administrador`  
**Cuando** consume `GET /api/companies`  
**Entonces** recibe error de autorizacion

### CA-3: Datos aislados por empresa
**Dado** un admin de empresa  
**Cuando** consulta usuarios, establecimientos, gastos o transacciones  
**Entonces** solo ve recursos de su empresa  
**Y** no puede ver recursos de otra empresa

### CA-4: Transacciones marcadas correctamente
**Dado** una transaccion creada desde la app  
**Entonces** debe persistirse con `company_id` y `establishment_id`

### CA-5: Dashboard inicial del superusuario no expone operacion ajena
**Dado** el superusuario autenticado  
**Cuando** ingresa al dashboard  
**Entonces** ve una vista de control  
**Y** no ve automaticamente el detalle operativo consolidado de otras empresas

### CA-6: Ingreso explicito al detalle de empresa
**Dado** el superusuario autenticado  
**Cuando** entra a `/empresas/{id}`  
**Entonces** puede ver:
- resumen de la empresa
- establecimientos de la empresa
- usuarios de la empresa
- historial de accesos

### CA-7: Auditoria de ingreso explicito
**Dado** que el superusuario entra al detalle de una empresa  
**Entonces** se debe insertar un registro en `company_access_logs`  
**Y** ese registro debe quedar visible en el overview de la empresa

### CA-8: Resumen global bloqueado para superusuario
**Dado** el superusuario autenticado  
**Cuando** intenta usar `GET /api/summary` sin empresa objetivo  
**Entonces** recibe `403`  
**Y** la UI no le ofrece esa vista como camino principal

## Criterios de interfaz

### CA-UI-1: Empresas no se muestran como franjas estiradas
**Dado** que existe una sola empresa o pocas empresas  
**Cuando** se renderiza la lista de empresas  
**Entonces** deben verse como tarjetas con ancho controlado  
**Y** no como bandas horizontales a todo el contenedor

### CA-UI-2: Lenguaje visual consistente
**Dado** dashboard, empresas, detalle de empresa, usuarios y resumen  
**Entonces** deben compartir:
- la misma paleta base
- encabezados con el mismo estilo
- tarjetas/paneles con radios y sombras consistentes

## Criterios de no regresion

### CA-NR-1: Build Angular exitoso
**Cuando** se ejecuta `npm run build` en `frontend/`  
**Entonces** el build termina sin errores

### CA-NR-2: Esquema aplicable
**Cuando** se ejecuta `php backend/scripts/apply_schema.php`  
**Entonces** la base se recrea sin errores

### CA-NR-3: Salud basica del backend
**Cuando** se valida sintaxis PHP de los archivos nuevos  
**Entonces** no deben existir errores de parseo

## Checks sugeridos

### Check 1: Aplicar esquema
```bash
php backend/scripts/apply_schema.php
```

### Check 2: Build frontend
```bash
cd frontend
npm run build
```

### Check 3: Ver overview con auditoria
```bash
curl -H "X-User-Id: 1" http://localhost/expense-tracker-back/api/companies/1
```
**Resultado esperado**: payload con `company`, `summary`, `establishments`, `users` y `accessLogs`

### Check 4: Ver bloqueo de resumen global para superusuario
```bash
curl -H "X-User-Id: 1" http://localhost/expense-tracker-back/api/summary?month=2026-04
```
**Resultado esperado**: `403`

## Condicion de completitud
✅ multiempresa operativo  
✅ superusuario con ingreso explicito por empresa  
✅ auditoria de acceso funcional  
✅ aislamiento por empresa para admin normal  
✅ build frontend exitoso  
✅ esquema aplicable  
✅ package de PR documentado
