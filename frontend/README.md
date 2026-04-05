
# Frontend

Frontend en Angular para el Expense Tracker, construido para consumir la API PHP del proyecto y servir como cara visible de un futuro SaaS.

## Responsabilidad

Este desarrollo contiene:

- login
- dashboard general
- vista por establecimiento
- resumen mensual
- administracion de usuarios
- integracion con la API del backend

## Stack tecnico

- Angular 19
- TypeScript
- Angular Router
- Angular HttpClient
- build publicado bajo Apache/WAMP

## Estructura base

```text
frontend/
├── public/
├── src/
│   ├── app/
│   │   ├── core/
│   │   ├── pages/
│   │   └── services/
│   ├── index.html
│   ├── main.ts
│   └── styles.css
├── angular.json
└── package.json
```

## Comandos utiles

Instalar dependencias:

```powershell
npm install
```

Levantar en desarrollo:

```powershell
npm start
```

Generar build:

```powershell
npm run build
```

## Publicacion local

La app se construye con `baseHref` para publicarse en:

- `http://localhost/expense-tracker/`

El build queda en:

- `frontend/dist/expense-traker/browser`

El `.htaccess` persistente se copia desde `frontend/public/.htaccess`.

## Integracion con backend

La comunicacion con la API se centraliza en:

- `src/app/services/api.service.ts`

La URL base usada actualmente es:

- `http://localhost/expense-tracker-back/api`

## Consideraciones SaaS

Desde frontend, el proyecto ya esta organizado para crecer hacia un SaaS:

- pantallas separadas por dominio funcional
- guards de acceso
- servicios desacoplados de la UI
- consumo por API, no por almacenamiento local del navegador

Los siguientes pasos recomendados para la evolucion SaaS son:

- layout mas robusto para multiempresa
- branding configurable por cliente
- onboarding de nuevas cuentas
- manejo de suscripcion y plan
- modulos adicionales por feature flag
  
