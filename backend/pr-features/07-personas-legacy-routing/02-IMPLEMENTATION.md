# Feature-07: Personas Module Legacy Routing - IMPLEMENTATION

## Archivos añadidos
- `app/Modules/Personas/PersonasController.php`
- `app/Modules/Personas/Services/PersonasService.php`
- `config/personas-actions.php`
- `tests/Unit/PersonasParametersTest.php`
- `tests/Unit/PersonasServiceTest.php`

## Archivos modificados
- `app/Bootstrap/Routes.php`
- `app/Core/Routing/Router.php`

## Cambios clave
- Se añadió módulo `Personas` con dos handlers legacy iniciales
- `Routes::map()` carga `personas-actions.php`
- `Router` instancia `PersonasController` mediante factory dedicada
- `PersonasService` implementa respuestas simuladas seguras mientras se prepara la integración real con BD/Odoo
