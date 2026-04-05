# Feature-11: Personas Response Alignment - SPECS

## Objetivo
Corregir el contrato de respuesta del módulo `personas` para que coincida con el payload legacy que consume el frontend actual.

## Requisitos Funcionales

### RF-01: `GET_MAESTROS_CLIENTES`
- La respuesta debe incluir `error` en raíz.
- Los maestros deben venir en `datos`.

### RF-02: `BUSCAR_ODOO_TITULO_PERSONA`
- La respuesta debe incluir `error` en raíz.
- La lista debe venir en `data`.

### RF-03: Alcance
- Solo se ajusta el contrato de salida.
- No se amplía funcionalidad nueva del módulo.
