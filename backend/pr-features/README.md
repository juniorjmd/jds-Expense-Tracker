# PR Features Structure

Este directorio contiene la documentación de todos los cambios (features) importantes del proyecto, organizados en carpetas numeradas.

Cada feature tiene 3 archivos obligatorios:

## Estructura de cada feature

```
pr-features/
├── 01-nombre-del-feature/
│   ├── 01-SPECS.md                    # Especificación del requisito
│   ├── 02-IMPLEMENTATION.md           # Detalles de implementación
│   └── 03-ACCEPTANCE_CRITERIA.md      # Criterios de aceptación y tests
├── 02-otro-feature/
│   ├── 01-SPECS.md
│   ├── 02-IMPLEMENTATION.md
│   └── 03-ACCEPTANCE_CRITERIA.md
└── ...
```

## Contenido de cada archivo

### 01-SPECS.md
- **Objetivo**: Qué se quiere lograr
- **Contexto**: Por qué es necesario
- **Requisitos funcionales**: Qué debe hacer
- **Requisitos no-funcionales**: Cómo debe comportarse
- **Reglas de negocio**: Restricciones y condiciones

### 02-IMPLEMENTATION.md
- **Archivos modificados**: Listado completo
- **Cambios detallados**: Código antes/después o pseudocódigo
- **Flujo de ejecución**: Cómo se ejecuta la feature
- **Compatibilidad**: Mapeos de parámetros
- **Ventajas**: Por qué esta implementación

### 03-ACCEPTANCE_CRITERIA.md
- **Criterios funcionales**: CA para cada requisito
- **Criterios de no-regresión**: Lo que no debe romperse
- **Criterios de performance**: Metrics aceptables
- **Criterios de mantenibilidad**: Estándares de código
- **Tests**: Cómo verificar cada criterio
- **Condición de completitud**: Cuándo está LISTO

## Features implementadas

### ✅ 01-auth-legacy-routing
**Estado**: Implementado  
**Descripción**: Soporte para acciones legacy de Auth compatible con frontend actual  
**Cambios principales**:
- Mapeo de 5 acciones legacy
- Router refactorizado para soportar ambos caminos
- AuthService compatible con parámetros legacy
- 3-5 líneas de cambios en múltiples archivos

**Archivos afectados**:
- `config/actions.php` (CREADO)
- `app/Bootstrap/Routes.php` (MODIFICADO)
- `app/Core/Routing/Router.php` (REFACTORIZADO)
- `app/Modules/Auth/Services/AuthService.php` (ACTUALIZADO)

---

## Cómo usar este repositorio

1. **Al iniciar una feature**:
   - Crear directorio: `pr-features/XX-nombre/`
   - Crear los 3 archivos (SPECS, IMPLEMENTATION, ACCEPTANCE_CRITERIA)
   - Documentar ANTES de implementar

2. **Durante la implementación**:
   - Seguir los requisitos de SPECS
   - Actualizar IMPLEMENTATION con cambios reales
   - Ejecutar tests de ACCEPTANCE_CRITERIA

3. **Antes de hacer commit**:
   - Verificar que todos los CA funcionales pasen
   - Verificar que no hay regresos
   - Guardar los 3 archivos en la carpeta de la feature

4. **En el PR**:
   - Referenciar los archivos de `pr-features/XX-nombre/`
   - Los reviewers pueden leer SPECS y CA directamente
   - No necesitan buscar en el código

---

## Ventajas de esta estructura

✅ **Claridad**: Requisitos y aceptación definidos antes de codear  
✅ **Trazabilidad**: Cada feature tiene su documentación  
✅ **Testing**: Tests documentados y verificables  
✅ **Mantenibilidad**: Futura referencia de por qué se hizo  
✅ **Onboarding**: Nuevos devs entienden rápidamente los cambios  
✅ **Code Review**: Reviewers tienen contexto completo  

---

## Próximas features esperadas

- [ ] 02-other-modules-legacy-routing (Carwash, Inventario, etc.)
- [ ] 03-api-rest-standardization
- [ ] 04-database-optimization
- [ ] 05-security-enhancements
- [ ] ...
