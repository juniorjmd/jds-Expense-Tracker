# Criterios de Aceptación: Auth Legacy Routing Support

## Criterios funcionales (DEBE cumplir todos)

### CA-1: Enrutamiento de acciones legacy
**Dado** que se envía una petición POST sin `/api`  
**Y** con un `action` válido mapeado en `config/actions.php`  
**Entonces** la petición debe ser enrutada al AuthController  
**Y** NO debe retornar error 404  

**Verificación**:
```bash
curl -X POST http://localhost:8000/ \
  -H "Content-Type: application/json" \
  -d '{"action":"ef2e1d89937fba9f888516293ab1e19e7ed789a5","_usuario":"admin","_password":"test"}'
```
**Resultado esperado**: Status 200, 401, o 422 (NO 404)

---

### CA-2: Aceptación de parámetros legacy `_usuario` y `_password`
**Dado** que `AuthService::login()` recibe un Request  
**Y** el Request contiene `_usuario` y `_password`  
**Entonces** debe extraer correctamente ambos parámetros  
**Y** no debe retornar error "usuario/password requeridos"  

**Verificación**:
```php
$request = new Request('POST', [], [
    'action' => 'login',
    '_usuario' => 'testuser',
    '_password' => 'testpass'
], [], ['REQUEST_METHOD' => 'POST']);

$result = (new AuthService())->login($request);
// $result['code'] debe ser algo diferente de 'VALIDATION_ERROR'
// (puede ser error de BD, pero no de validación de parámetros)
```

---

### CA-3: Compatibilidad de parámetros con `input()`
**Dado** que el `AuthService` usa `$request->input('usuario', $request->input('_usuario', ''))`  
**Entonces** debe priorizar `usuario` sobre `_usuario`  
**Y** cuando solo `_usuario` existe, debe usarlo  

**Verificación**:
```php
// Test 1: Solo _usuario
$req1 = new Request('POST', [], ['_usuario' => 'user1'], []);
assert($req1->input('usuario', $req1->input('_usuario', '')) === 'user1');

// Test 2: Ambos presentes (prioridad a usuario)
$req2 = new Request('POST', [], ['usuario' => 'user2', '_usuario' => 'user1'], []);
assert($req2->input('usuario', $req2->input('_usuario', '')) === 'user2');
```

---

### CA-4: Las 5 acciones Auth mapeadas correctamente
**Dado** que se cargan las rutas con `Routes::map()`  
**Entonces** deben existir 5 acciones Auth  
**Y** cada una debe ser callable  

**Verificación**:
```php
$actionMap = Routes::map();
$actions = [
    'ef2e1d89937fba9f888516293ab1e19e7ed789a5',
    '16770d92a6a82ee846f7ff23b4c8ad05b69fba03',
    '16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03',
    'RESETEAR_USUARIO_PASS',
    'HIJODELAGRANCHINGADA'
];

foreach ($actions as $action) {
    assert(isset($actionMap[$action]), "Action $action no mapeada");
    assert(is_callable($actionMap[$action]), "Action $action no es callable");
}
```

---

### CA-5: Router redirige a legacy cuando no hay `/api`
**Dado** que se envía una petición sin `/api` en la ruta  
**Y** con `action` en el body  
**Entonces** `Router::dispatch()` debe llamar a `dispatchLegacyAction()`  
**Y** NO debe fallar con 'Prefijo api requerido'  

**Verificación**:
```
Petición: POST /
Body: {"action": "ef2e1d89..."}

Resultado: NO error "Prefijo api requerido"
Resultado: SÍ error "ACTION_NOT_FOUND" o similar (pero está buscando la acción)
```

---

### CA-6: Router redirige a API cuando hay `/api`
**Dado** que se envía una petición con `/api` en la ruta  
**Entonces** `Router::dispatch()` debe procesar como API REST  
**Y** debe buscar el módulo y método correspondiente  

**Verificación**:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin","password":"test"}'
```
**Resultado esperado**: Procesa con AuthController (mismo que legacy)

---

### CA-7: AuthContext sigue resolviendo tokens legacy
**Dado** que se envía `_llaveSession` en el body  
**Entonces** `AuthContext::resolveToken()` debe encontrarlo  
**Y** no debe buscar solo `key_registro` o Bearer token  

**Verificación**:
```php
$request = new Request('POST', [], ['_llaveSession' => 'token123'], []);
$context = new AuthContext();
$token = $context->resolveToken($request); // Usar reflection si es needed
assert($token === 'token123');
```

---

## Criterios de no-regresión (DEBE cumplir todos)

### CA-NR-1: API REST `/api/auth/login` sigue funcionando
**Dado** que se envía POST a `/api/auth/login`  
**Y** con parámetros `usuario` y `password`  
**Entonces** debe retornar la misma respuesta que antes  
**Y** NO debe estar afectada por los cambios de legacy routing  

---

### CA-NR-2: Otras acciones legacy (no Auth) siguen siendo null
**Dado** que se intenta acceder a una acción legacy no mapeada  
**Y** que no está en `config/actions.php`  
**Entonces** debe retornar error 'ACTION_NOT_FOUND'  
**Y** NO debe causar exception o error 500  

---

### CA-NR-3: PING action sigue funcionando
**Dado** que la acción `PING` está en `Routes::map()`  
**Entonces** debe seguir siendo accessible  
**Y** debe retornar `{"pong": true, ...}`  

---

## Criterios de performance (DEBE cumplir todos)

### CA-PERF-1: Sin overhead significativo en consultas DB
**Dado** que se habilita legacy routing  
**Entonces** las consultas DB deben ser las mismas  
**Y** no debe haber queries adicionales innecesarias  

**Verificación**: Comparar queries antes vs después con xDebug o logs

---

### CA-PERF-2: Sin overhead en parsing de rutas
**Dado** que el router ahora verifica si hay `/api`  
**Entonces** el tiempo adicional debe ser < 1ms  

**Verificación**: Medir tiempo de `parse_url()` + búsqueda de `/api`

---

## Criterios de mantenibilidad (DEBE cumplir)

### CA-MAINT-1: Lógica centralizada en AuthService
**Dado** que el login puede venir por dos caminos  
**Entonces** la lógica de negocio NO debe estar duplicada  
**Y** ambos caminos deben llamar al mismo `AuthService::login()`  

**Verificación**: Búsqueda en codebase: `AuthService::login` debe ser el único lugar

---

### CA-MAINT-2: Configuración en archivo dedicado
**Dado** que hay acciones legacy mapeadas  
**Entonces** deben estar en `config/actions.php`  
**Y** NO en hardcode dentro del Router o Routes  

---

### CA-MAINT-3: Métodos de Router claros
**Dado** que el Router ahora tiene más lógica  
**Entonces** debe tener métodos privados que separen concerns  
**Y** `dispatchLegacyAction()` debe existir como método separado  

---

## Tests a ejecutar

### Test 1: Validación de parámetros
```bash
cd jds-backend-app-2026
php test-login-legacy.php
```
**Resultado esperado**: Todos los checkmarks ✓

---

### Test 2: Verificación de acciones mapeadas
```php
$map = Routes::map();
$required = [
    'ef2e1d89937fba9f888516293ab1e19e7ed789a5',
    '16770d92a6a82ee846f7ff23b4c8ad05b69fba03',
    '16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03',
    'RESETEAR_USUARIO_PASS',
    'HIJODELAGRANCHINGADA'
];
foreach ($required as $action) {
    assert(isset($map[$action]), "Missing: $action");
}
echo "✓ All Auth actions mapped correctly\n";
```

---

### Test 3: HTTP Integration (cuando BD esté disponible)
```bash
# Legacy login
curl -X POST http://localhost:8000/ \
  -H "Content-Type: application/json" \
  -d '{"action":"ef2e1d89937fba9f888516293ab1e19e7ed789a5","_usuario":"admin","_password":"admin"}'

# Expected: 200 or 401 (not 404)
```

---

### Test 4: API REST login (verificar no-regresión)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin","password":"admin"}'

# Expected: Same response as legacy
```

---

## Condición de completitud

✅ Todos los CA funcionales cumplidos  
✅ Todos los CA de no-regresión cumplidos  
✅ Todos los CA de performance cumplidos  
✅ Todos los CA de mantenibilidad cumplidos  
✅ Tests automatizados ejecutados exitosamente  
✅ Code review aprobado  
✅ Documentación actualizada  

**ENTONCES**: Feature está LISTO para MERGE
