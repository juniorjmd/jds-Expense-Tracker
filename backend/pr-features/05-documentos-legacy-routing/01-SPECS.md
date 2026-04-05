# Feature-05: Documentos Module Legacy Routing - SPECS

## 📋 Objetivo
Implementar compatibilidad backward-compatible para acciones legacy del módulo Documentos, permitiendo que el frontend actual continúe accediendo a la gestión documental mientras se avanza en la modernización del backend.

## 🎯 Requisitos Funcionales

### RF-01: Mapeo de Acciones Legacy
El sistema debe mapear 4 acciones legacy del módulo Documentos a sus handlers:

| Acción | Hash/ID | Método Esperado | Descripción |
|--------|---------|-----------------|-------------|
| Listar documentos | `LISTAR_DOCUMENTOS` | `DocumentosController::listDocuments()` | Devuelve documentos disponibles para el usuario |
| Subir documento | `SUBIR_DOCUMENTO` | `DocumentosController::uploadDocument()` | Recibe datos de archivo y lo guarda en el sistema |
| Descargar documento | `DESCARGAR_DOCUMENTO` | `DocumentosController::downloadDocument()` | Devuelve metadata y enlace de descarga |
| Eliminar documento | `BORRAR_DOCUMENTO` | `DocumentosController::deleteDocument()` | Elimina un documento de forma lógica |

### RF-02: Parámetros Legacy
El módulo Documentos debe aceptar parámetros con prefijo `_`:

**Listar documentos**
```json
{
  "action": "LISTAR_DOCUMENTOS",
  "_usuario_id": 123,
  "_tipo": "factura"
}
```

**Subir documento**
```json
{
  "action": "SUBIR_DOCUMENTO",
  "_usuario_id": 123,
  "_nombre": "factura-123.pdf",
  "_tipo": "factura",
  "_contenido_base64": "..."
}
```

**Descargar documento**
```json
{
  "action": "DESCARGAR_DOCUMENTO",
  "_documento_id": 456
}
```

**Eliminar documento**
```json
{
  "action": "BORRAR_DOCUMENTO",
  "_documento_id": 456
}
```

## 🔒 Requisitos de Seguridad
- Todas las acciones requieren usuario autenticado
- Verificar que el usuario tenga acceso al documento solicitado
- No exponer rutas directas de archivos sin autorización

## 📊 Requisitos No Funcionales
- Respuestas JSON consistentes con el resto del backend
- Manejo de errores claro y estable
- Configuración de legacy actions en un archivo separado
