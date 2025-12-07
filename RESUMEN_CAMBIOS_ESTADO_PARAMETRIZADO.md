# Resumen de Cambios Implementados

## Objetivo
Parametrizar el campo `estado` de la tabla `complementarios_ofertados` para eliminar el código hardcodeado y convertirlo en una relación FK a `parametros_temas`.

## Cambios Realizados

### 1. Migración: `2025_12_07_131537_parametrizar_estado_complementarios_ofertados.php`

**Ubicación**: `database/migrations/batch_17_complementarios/`

**Funcionalidad**:
- Crea el tema "ESTADO_PROGRAMA_COMPLEMENTARIO" en la tabla `temas`
- Crea los parámetros: "Sin Oferta", "Con Oferta", "Cupos Llenos" en la tabla `parametros`
- Crea las relaciones en `parametros_temas` entre el tema y los parámetros
- Modifica la tabla `complementarios_ofertados`:
  - Renombra la columna `estado` → `estado_old` (backup)
  - Agrega nueva columna `estado_id` como FK a `parametros_temas`
  - Migra datos existentes mapeando valores legacy (0,1,2) a IDs de `parametros_temas`
  - Elimina la columna `estado_old`

**Rollback completo**: Incluye reversión de todos los cambios y eliminación de datos creados.

### 2. Modelo: `ComplementarioOfertado.php`

**Ubicación**: `app/Models/Complementarios/ComplementarioOfertado.php`

**Cambios**:
- **Campo `$fillable`**: Reemplazado `'estado'` por `'estado_id'`
- **Nueva relación**: Agregado método `estado()` que retorna `belongsTo(ParametroTema::class, 'estado_id')`
- **Accessor de compatibilidad**: 
  - `getEstadoAttribute()`: Devuelve valor numérico legacy (0,1,2) basado en el nombre del parámetro
  - Mantiene compatibilidad con código existente que espera el campo `estado`
- **Accessors actualizados**:
  - `getEstadoLabelAttribute()`: Ahora usa `$this->estado->parametro->name` en lugar de match hardcodeado
  - `getBadgeClassAttribute()`: Ahora usa el nombre del estado desde la relación

### 3. Fábrica: `ComplementarioOfertadoFactory.php`

**Ubicación**: `database/factories/ComplementarioOfertadoFactory.php`

**Cambios**:
- **Definición principal**: Reemplazado `'estado'` por `'estado_id'`
- **Lógica de estado**: Ahora busca `ParametroTema` del tema "ESTADO_PROGRAMA_COMPLEMENTARIO"
- **Métodos de estado actualizados**:
  - `sinOferta()`: Usa `getEstadoIdByName('Sin Oferta')`
  - `conOferta()`: Usa `getEstadoIdByName('Con Oferta')`
  - `cuposLlenos()`: Usa `getEstadoIdByName('Cupos Llenos')`
  - `conCupos()`: Actualizado para usar `estado_id`
- **Nuevo helper**: `getEstadoIdByName()` para obtener ID de `ParametroTema` por nombre

## Estructura de Datos Resultante

### Antes (Hardcodeado):
```sql
complementarios_ofertados.estado: TINYINT
Valores: 0 = Sin Oferta, 1 = Con Oferta, 2 = Cupos Llenos
```

### Después (Parametrizado):
```sql
temas: 
  - id: [auto], name: 'ESTADO_PROGRAMA_COMPLEMENTARIO'

parametros:
  - id: [auto], name: 'Sin Oferta'
  - id: [auto], name: 'Con Oferta'  
  - id: [auto], name: 'Cupos Llenos'

parametros_temas:
  - id: [auto], tema_id: [id_tema], parametro_id: [id_parametro]

complementarios_ofertados.estado_id: FOREIGN KEY → parametros_temas.id
```

## Beneficios Obtenidos

1. **Consistencia con el sistema**: Ahora sigue el mismo patrón de parametrización que otras partes de la aplicación
2. **Mantenibilidad**: Los estados pueden modificarse sin cambiar código (agregar/eliminar estados desde la BD)
3. **Auditoría**: Los estados ahora están registrados en `parametros_temas` que incluye campos `user_create_id`, `user_edit_id`
4. **Compatibilidad**: Se mantiene acceso al valor legacy mediante el accessor `$complementario->estado`
5. **Flexibilidad**: Fácil agregar nuevos estados en el futuro

## Próximos Pasos Recomendados

### 1. Ejecutar la migración:
```bash
php artisan migrate --path=database/migrations/batch_17_complementarios
```

### 2. Actualizar controladores y vistas:
- Revisar que todas las referencias a `ComplementarioOfertado::estado` sigan funcionando
- Actualizar formularios para usar `estado_id` en lugar de `estado`

### 3. Parametrizar `aspirantes_complementarios.estado`:
- Aplicar el mismo patrón para los estados de aspirantes (1=En proceso, 3=Admitido, 4=Rechazado)

### 4. Agregar campos de auditoría:
- Considerar agregar `user_create_id` y `user_edit_id` a `complementarios_ofertados` (no incluido en este cambio)

## Verificación de Cambios

### Archivos modificados:
1. ✅ `database/migrations/batch_17_complementarios/2025_12_07_131537_parametrizar_estado_complementarios_ofertados.php`
2. ✅ `app/Models/Complementarios/ComplementarioOfertado.php`
3. ✅ `database/factories/ComplementarioOfertadoFactory.php`

### Estado de migración:
- La migración está creada y lista para ejecutar (status: Pending)
- Se puede ejecutar con `php artisan migrate --path=database/migrations/batch_17_complementarios`

## Notas Técnicas

1. **Compatibilidad hacia atrás**: El accessor `getEstadoAttribute()` asegura que código existente que espera `$complementario->estado` (valor 0,1,2) siga funcionando
2. **Relaciones cargadas**: Para óptimo rendimiento, usar `with('estado.parametro')` cuando se necesiten los nombres de estado
3. **Valores por defecto**: Si no hay relación `estado`, se retorna 0 (Sin Oferta) como valor por defecto
4. **Manejo de errores**: Todos los cambios incluyen try-catch para evitar errores si las tablas no existen

Este cambio elimina efectivamente el código hardcodeado del estado de programas complementarios y lo alinea con la arquitectura de parametrización del sistema.
