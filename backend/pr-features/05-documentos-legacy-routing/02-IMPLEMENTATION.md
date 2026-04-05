# Feature-05: Documentos Module Legacy Routing - IMPLEMENTATION

## Arquitectura implementada

### Estructura de archivos

```text
app/Modules/Documentos/
├── DocumentosController.php
├── Repositories/
│   └── DocumentosRepository.php
└── Services/
    └── DocumentosService.php

config/
└── documentos-actions.php
```

### Cambios reales del cierre actual

- `DocumentosRepository` ya llama los procedimientos legacy reales:
  - `getUserGenericDocuments`
  - `crearNuevoDocumento`
  - `crearNuevoDocumentoCompra`
  - `cambiarDocumentoActual`
  - `cambiarDocumentoCompraActual`
- `DocumentosService` ya no devuelve payload legacy crudo.
- Las respuestas nuevas del modulo salen estandarizadas como `ok/data/error`.
- Para colecciones de documentos el backend devuelve:
  - `data.records`
  - `data.count`
- Para creacion/cambio de documento devuelve:
  - `data.message`
  - `data.documentId`
  - y en creacion tambien `data.records` y `data.count`

### Acciones legacy cerradas en este frente

- `GET_DOCUMENTOS_USUARIO_ACTUAL`
- `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA`
- `CREAR_DOCUMENTO_POR_USUARIO`
- `CREAR_DOCUMENTO_COMPRA_POR_USUARIO`
- `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO`
- `CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO`

### Validacion real local

- login real contra `http://localhost/jds_back_2026/api/login/`
- `POST /api/documentos/` con `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA` responde `ok: true`
- `POST /api/documentos/` con `CREAR_DOCUMENTO_POR_USUARIO` responde `ok: true`
- `POST /api/documentos/` con `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO` responde `ok: true`

## Tests

- `DocumentosParametersTest`
- `DocumentosServiceTest`
- `php tests/run-tests.php` en verde
