# Tests Structure

Estructura de pruebas organizadas por tipo:

```
tests/
├── Unit/                          # Pruebas unitarias (sin dependencias externas)
│   ├── AuthParametersTest.php     # Validación de parámetros legacy
│   └── AuthServiceTest.php        # Lógica del AuthService
│
├── Integration/                   # Pruebas de integración (requieren BD, servicios externos)
│   └── DatabaseConnectionTest.php # Conexión y validación de BD
│
├── README.md                      # Este archivo
└── run-tests.php                  # Script para ejecutar todos los tests
```

## Ejecutar tests

### Todos los tests
```bash
php run-tests.php
```

### Solo tests unitarios
```bash
php Unit/AuthParametersTest.php
php Unit/AuthServiceTest.php
```

### Solo tests de integración
```bash
php Integration/DatabaseConnectionTest.php
```

## Descripción de cada test

### Unit/AuthParametersTest.php
✓ Validación de parámetros legacy (_usuario, _password, _llaveSession)  
✓ Detección correcta de acciones en el request  
✓ Prioridad de parámetros (moderno > legacy)  
**Requisitos**: Ninguno (no necesita BD)  

### Unit/AuthServiceTest.php
✓ Login con parámetros legacy  
✓ Loop completo de controller  
✓ Compatibilidad backwards  
**Requisitos**: Ninguno (usa mocks)  

### Integration/DatabaseConnectionTest.php
✓ Conexión a BD MySQL  
✓ Acceso a tablas  
✓ Acceso a vistas  
✓ Existencia de procedimientos almacenados  
**Requisitos**: Conectividad a mysql.us.stackcp.com:42363  

## Agregar nuevas pruebas

1. Crear archivo `{NombreDelTest}Test.php` en `Unit/` o `Integration/`
2. Asegurase que el test `exit(0)` al finalizar exitosamente o `exit(1)` si falla
3. El script `run-tests.php` lo ejecutará automáticamente
