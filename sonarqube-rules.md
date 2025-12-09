# Reglas de SonarQube para Laravel/PHP

## 1. Complejidad Cognitiva (Cognitive Complexity)

**Regla:** Los métodos no deben ser demasiado complejos.

### ✔ Qué significa
Evita funciones largas con demasiados condicionales, loops, nested if, etc.

### ✔ Cómo corregir
- Extrae bloques en métodos privados
- Evita condicionales anidados
- Divide funciones grandes en varias pequeñas

### ❌ Malo
```php
public function destroy($id) {
    if ($x) {
        if ($y) {
            if ($z) {
                // ...
            }
        }
    }
}
```

### ✔ Bueno
```php
public function destroy($id) {
    $this->validarPermisos($id);
    $this->eliminarRelaciones($id);
    $this->eliminar($id);
}
```

---

## 🔥 2. Complejidad Ciclomática (Cyclomatic Complexity)

**Regla:** Evitar demasiadas rutas lógicas en una función.

Similar a la anterior pero basada en cantidad de decisiones.

---

## 🔥 3. Duplicación de Código (Duplicated Code)

**Regla:** No repetir bloques idénticos.

### ❌ Malo:
```php
$user = Auth::user();
$role = $user->role;
$status = $user->status;
```

### ✔ Bueno:
```php
$user = Auth::user();
```

---

## 🔥 4. Validaciones de Null (Null Checks)

**Regla:** No asumir que un objeto existe.

### ❌ Malo
```php
return $programa->nombre;
```

### ✔ Bueno
```php
return $programa?->nombre ?? 'N/A';
```

---

## 🔥 5. Manejo de Excepciones (Exceptions Handling)

**Regla:** Nunca dejes un catch vacío, ni uses dd() en producción.

### ❌ Malo
```php
catch (\Exception $e) {}
```

### ✔ Bueno
```php
catch (\Exception $e) {
    Log::error("Error eliminando programa", ['error' => $e->getMessage()]);
    return back()->with('error', 'No se pudo eliminar el programa.');
}
```

---

## 🔥 6. Seguridad: No exponer datos sensibles

**Evita:**
- Imprimir errores completos al usuario
- Exponer tokens
- Exponer información de la BD

---

## 🔥 7. Inyección SQL (SQL Injection)

**Regla:** nunca construir queries manuales sin bindings.

### ❌ Malo
```php
DB::select("SELECT * FROM users WHERE id = $id");
```

### ✔ Bueno
```php
DB::select("SELECT * FROM users WHERE id = ?", [$id]);
```

---

## 🔥 8. Funciones muy largas

**Límite recomendado:** máximo 20–30 líneas por método.

Laravel te motiva naturalmente a dividir funciones en:
- Servicios
- Repositorios
- Helpers

---

## 🔥 9. Uso adecuado de Tipado en PHP 8+

### ✔ SonarQube recomienda:
- Tipos de retorno
- Tipos en parámetros

### Ejemplo:
```php
public function index(): View 
```

---

## 🔥 10. No dejar código muerto (Dead Code)

**Evita:**
- Variables no usadas
- Métodos que nunca se llaman
- Imports no utilizados

### ❌ Malo
```php
use Illuminate\Support\Facades\DB; // <--- sin usar
```

---

## 🔥 11. Comentarios innecesarios o engañosos

Evitar comentarios que repiten lo obvio.

### ❌ Malo
```php
// obtiene los programas
$programas = Programa::all();
```

### ✔ Bueno (solo si aporta valor)
```php
// Se obtienen solo los programas activos para la vista pública
$programas = Programa::activos()->get();
```

---

## 🔥 12. Buenas prácticas en pruebas (Tests)

- No repetir factories
- No copiar bloques enteros
- Usar helpers
- Nombres descriptivos en tests

### ✔ Bueno
```php
#[Test]
public function admin_puede_listar_programas()
```

---

## 🔥 13. No abusar de facades cuando puedes usar inyección de dependencias

---

## 🔥 14. Código limpio (Clean Code)

SonarQube mide:
- Nombres claros de métodos/variables
- Funciones pequeñas
- Responsabilidades únicas

---

*Documento generado para referencia rápida de reglas de SonarQube en proyectos Laravel/PHP.*
