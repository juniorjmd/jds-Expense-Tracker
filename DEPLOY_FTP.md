# Despliegue FTP

Este proyecto requiere una verificacion manual antes de subir cambios a produccion por FTP.

## Credenciales FTP locales

No guardar usuarios, claves ni tokens reales en este archivo porque puede subirse al repositorio.
Las credenciales reales deben quedar solo en el equipo local, en:

```text
.deploy/ftp.local.env
```

Formato esperado:

```text
FTP_HOST=ftp.ejemplo.com
FTP_PORT=21
FTP_USER=usuario
FTP_PASSWORD=clave
FTP_FRONTEND_PATH=/ruta/remota/frontend
FTP_BACKEND_PATH=/ruta/remota/backend
```

Ese archivo esta ignorado por git. Si se usa otro paquete versionado, tambien se puede copiar como `.deploy_v1001/ftp.local.env`, que queda ignorado por el patron `.deploy_v*/ftp.local.env`.

## Regla de version

- Revisar `VERSION` antes de preparar el despliegue.
- Si la version no cambia, actualizar la carpeta de despliegue vigente.
- Si la version cambia, crear o usar una carpeta consistente con esa version, por ejemplo `.deploy_v1001`.
- No dejar carpetas nuevas de release si el numero de version sigue siendo el mismo.

## Despliegue backend

- Identificar primero si el cambio es solo backend o si tambien exige UI en frontend.
- Para cambios de API, sincronizar al paquete `.deploy_vXXXX/backend` unicamente los archivos reales modificados.
- Revisar siempre el `.env` de produccion dentro del paquete antes de subir:
  - `APP_URL`
  - `APP_FRONTEND_URL`
  - `APP_CORS_ALLOWED_ORIGINS`
  - `DB_HOST`
  - `DB_PORT`
  - `DB_DATABASE`
  - `DB_USERNAME`
  - `DB_PASSWORD`
- Si `DB_PORT` no aplica en produccion, dejarlo vacio o con `default`.
- Si el cambio agrega rutas nuevas, confirmar que el archivo de rutas del paquete FTP tambien quede actualizado.
- Si el cambio depende de nuevas tablas o columnas, preparar y validar primero la migracion o ajuste SQL que vaya a ejecutarse en el entorno real.
- Para el endpoint de cambio de contrasena del usuario autenticado, los archivos backend que deben viajar juntos son:
  - `backend/app/Bootstrap/Routes.php`
  - `backend/app/Controllers/UserController.php`
  - `backend/app/Repositories/UserRepository.php`
  - `backend/app/Services/UserService.php`

## Preparacion frontend

- Confirmar que el login y cualquier cambio visual ya existen en `frontend/src/`.
- Ejecutar `npm run build` dentro de `frontend/`.
- Verificar que el build generado en `frontend/dist/expense-traker/browser` tenga el `index.html` correcto.
- Confirmar que el `index.html` generado use el `base href` esperado para produccion.
- Copiar al paquete de despliegue los archivos actuales del build:
  - `.htaccess`
  - `index.html`
  - `main-*.js`
  - `polyfills-*.js`
  - `styles-*.css`
  - `runtime-config.js`
- Antes de subir por FTP, eliminar del paquete de despliegue los `main-*.js` viejos para no dejar basura ni referencias antiguas.
- Si el cambio incluye una pantalla nueva, sincronizar tambien sus fuentes TypeScript antes del build en el paquete versionado para que quede trazabilidad de que se subio.
- No publicar credenciales demo ni accesos precargados en login, README o ayudas visuales.
- Si se cambian semillas o limpieza de base, confirmar primero desde que entorno se ejecutara el reset real.

## Validaciones minimas antes de FTP

- Confirmar que `https://expense-tracker-php.sofdla.net/api/health` responda.
- Confirmar que `https://expense-tracker-php.sofdla.net/api/categories` responda datos desde base.
- Si el frontend en produccion sigue mostrando un build viejo, inspeccionar el HTML servido y revisar que el dominio este apuntando al `main-*.js` nuevo.

## Orden recomendado

1. Actualizar codigo fuente.
2. Ejecutar build de frontend.
3. Sincronizar `.deploy_vXXXX` con backend y frontend actuales.
4. Revisar `.env` de produccion en el paquete FTP.
5. Subir por FTP.
6. Validar en dominio frontend y backend que el cambio realmente quedo publicado.

## Checklist rapido para este cambio de contrasena

1. Sincronizar los archivos backend del endpoint a `.deploy_v1001/backend`.
2. Si se va a publicar tambien la pantalla Angular, ejecutar `npm run build` y copiar el nuevo `main-*.js` a `.deploy_v1001/frontend`.
3. Subir por FTP los archivos backend cambiados.
4. Probar autenticado el `POST /api/users/change-password`.
5. Si tambien se publico frontend, entrar con superusuario y validar la pantalla de cambio de clave.

## Nota operativa

Si el servidor web muestra una version vieja del login, no asumir que el cambio no existe en el repo: primero revisar si el hosting sigue sirviendo un `main-*.js` anterior o un `index.html` desactualizado.
