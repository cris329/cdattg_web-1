# MER - Programas Complementarios (Enfoque RF-ASP-006 y RF-PC-001)

## Observaciones Preliminares

1. **Campos de auditoría faltantes**: Varias tablas carecen de `user_create_id` y `user_edit_id` que sí están presentes en otras tablas del sistema (ej: `personas`, `parametros_temas`).
2. **Código hardcodeado**: El campo `estado` en `complementarios_ofertados` usa valores numéricos (0, 1, 2) en lugar de ser una FK a `parametros_temas`.
3. **Consistencia referencial**: Algunas relaciones podrían mejorarse para mantener consistencia con el patrón de parametrización del sistema.

## Tablas Principales (Creación/Modificación)

### Tabla: complementarios_ofertados
```sql
CREATE TABLE complementarios_ofertados (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(255) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    justificacion TEXT NULL,
    requisitos_ingreso TEXT NULL,
    duracion INTEGER NOT NULL,
    cupos INTEGER NOT NULL,
    estado TINYINT DEFAULT 0, -- ⚠️ Hardcodeado: 0=Sin Oferta, 1=Con Oferta, 2=Cupos Llenos
    modalidad_id INTEGER NOT NULL, -- FK a parametros_temas
    jornada_id INTEGER NOT NULL, -- FK a jornadas_formacion
    ambiente_id INTEGER NULL, -- FK a ambientes
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    -- ❌ FALTAN: user_create_id, user_edit_id (campos de auditoría)
    
    FOREIGN KEY (modalidad_id) REFERENCES parametros_temas(id),
    FOREIGN KEY (jornada_id) REFERENCES jornadas_formacion(id),
    FOREIGN KEY (ambiente_id) REFERENCES ambientes(id) ON DELETE SET NULL
);
```

### Tabla: aspirantes_complementarios
```sql
CREATE TABLE aspirantes_complementarios (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    persona_id INTEGER NOT NULL, -- FK a personas
    complementario_id INTEGER NOT NULL, -- FK a complementarios_ofertados
    observaciones TEXT NULL,
    estado INTEGER DEFAULT 1, -- ⚠️ Hardcodeado: 1=En proceso, 3=Admitido, 4=Rechazado
    documento_identidad_path VARCHAR(500) NULL,
    documento_identidad_nombre VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    -- ❌ FALTAN: user_create_id, user_edit_id (campos de auditoría)
    
    UNIQUE KEY aspirantes_complementarios_persona_programa_unique (persona_id, complementario_id),
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (complementario_id) REFERENCES complementarios_ofertados(id) ON DELETE CASCADE ON UPDATE CASCADE
);
```

### Tabla: persona_caracterizacion (pivote para caracterizaciones complementarias)
```sql
CREATE TABLE persona_caracterizacion (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    persona_id INTEGER NOT NULL, -- FK a personas
    parametro_id INTEGER NOT NULL, -- FK a parametros
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    -- ❌ FALTAN: user_create_id, user_edit_id (campos de auditoría)
    
    UNIQUE KEY persona_caracterizacion_unique (persona_id, parametro_id),
    FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
    FOREIGN KEY (parametro_id) REFERENCES parametros(id) ON DELETE CASCADE
);
```

## Tablas de Relación Many-to-Many (con auditoría completa)

### Tabla: competencia_complementario
```sql
CREATE TABLE competencia_complementario (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    competencia_id INTEGER NULL, -- FK a competencias
    complementario_id INTEGER NULL, -- FK a complementarios_ofertados
    user_create_id INTEGER NULL, -- FK a users ✅
    user_edit_id INTEGER NULL, -- FK a users ✅
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY competencia_complementario_unique (competencia_id, complementario_id),
    FOREIGN KEY (competencia_id) REFERENCES competencias(id),
    FOREIGN KEY (complementario_id) REFERENCES complementarios_ofertados(id),
    FOREIGN KEY (user_create_id) REFERENCES users(id),
    FOREIGN KEY (user_edit_id) REFERENCES users(id)
);
```

### Tabla: resultado_aprendizaje_complementario
```sql
CREATE TABLE resultado_aprendizaje_complementario (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    rap_id INTEGER NOT NULL, -- FK a resultados_aprendizajes
    complementario_id INTEGER NOT NULL, -- FK a complementarios_ofertados
    user_create_id INTEGER NULL, -- FK a users ✅
    user_edit_id INTEGER NULL, -- FK a users ✅
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY rap_complementario_unique (rap_id, complementario_id),
    FOREIGN KEY (rap_id) REFERENCES resultados_aprendizajes(id),
    FOREIGN KEY (complementario_id) REFERENCES complementarios_ofertados(id),
    FOREIGN KEY (user_create_id) REFERENCES users(id),
    FOREIGN KEY (user_edit_id) REFERENCES users(id)
);
```

### Tabla: rap_competencia_complementario
```sql
CREATE TABLE rap_competencia_complementario (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    rap_id INTEGER NOT NULL, -- FK a resultados_aprendizajes
    competencia_id INTEGER NOT NULL, -- FK a competencias
    complementario_id INTEGER NOT NULL, -- FK a complementarios_ofertados
    user_create_id INTEGER NULL, -- FK a users ✅
    user_edit_id INTEGER NULL, -- FK a users ✅
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY rap_competencia_complementario_unique (rap_id, competencia_id, complementario_id),
    FOREIGN KEY (rap_id) REFERENCES resultados_aprendizajes(id),
    FOREIGN KEY (competencia_id) REFERENCES competencias(id),
    FOREIGN KEY (complementario_id) REFERENCES complementarios_ofertados(id),
    FOREIGN KEY (user_create_id) REFERENCES users(id),
    FOREIGN KEY (user_edit_id) REFERENCES users(id)
);
```

### Tabla: guia_aprendizaje_complementario
```sql
CREATE TABLE guia_aprendizaje_complementario (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    guia_aprendizaje_id INTEGER NOT NULL, -- FK a guia_aprendizajes
    complementario_id INTEGER NOT NULL, -- FK a complementarios_ofertados
    user_create_id INTEGER NULL, -- FK a users ✅
    user_edit_id INTEGER NULL, -- FK a users ✅
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY guia_complementario_unique (guia_aprendizaje_id, complementario_id),
    FOREIGN KEY (guia_aprendizaje_id) REFERENCES guia_aprendizajes(id),
    FOREIGN KEY (complementario_id) REFERENCES complementarios_ofertados(id),
    FOREIGN KEY (user_create_id) REFERENCES users(id),
    FOREIGN KEY (user_edit_id) REFERENCES users(id)
);
```

## Tablas de Referencia (Solo lectura/validación)

### Tabla: parametros_temas
```sql
CREATE TABLE parametros_temas (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    tema_id INTEGER NOT NULL, -- FK a temas
    parametro_id INTEGER NOT NULL, -- FK a parametros
    status BOOLEAN DEFAULT 1,
    user_create_id INTEGER NULL, -- FK a users ✅
    user_edit_id INTEGER NULL, -- FK a users ✅
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE,
    FOREIGN KEY (parametro_id) REFERENCES parametros(id) ON DELETE CASCADE,
    FOREIGN KEY (user_create_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (user_edit_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### Tabla: jornadas_formacion
```sql
CREATE TABLE jornadas_formacion (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabla: ambientes
```sql
CREATE TABLE ambientes (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    piso_id INTEGER NULL,
    user_create_id INTEGER NULL, -- FK a users
    user_edit_id INTEGER NULL, -- FK a users
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Relaciones Principales

### Relaciones de creación (tablas que se modifican)
```
1. aspirantes_complementarios.persona_id → personas.id (CASCADE, CASCADE)
2. aspirantes_complementarios.complementario_id → complementarios_ofertados.id (CASCADE, CASCADE)
3. persona_caracterizacion.persona_id → personas.id (CASCADE)
4. persona_caracterizacion.parametro_id → parametros.id (CASCADE)
```

### Relaciones de validación (tablas de referencia)
```
1. complementarios_ofertados.modalidad_id → parametros_temas.id
2. complementarios_ofertados.jornada_id → jornadas_formacion.id
3. complementarios_ofertados.ambiente_id → ambientes.id (SET NULL)
4. parametros_temas.parametro_id → parametros.id (CASCADE)
5. parametros_temas.tema_id → temas.id (CASCADE)
```

## Recomendaciones de Mejora

### 1. Agregar campos de auditoría a tablas principales
```sql
-- Para complementarios_ofertados
ALTER TABLE complementarios_ofertados
ADD COLUMN user_create_id INTEGER NULL AFTER ambiente_id,
ADD COLUMN user_edit_id INTEGER NULL AFTER user_create_id,
ADD FOREIGN KEY (user_create_id) REFERENCES users(id),
ADD FOREIGN KEY (user_edit_id) REFERENCES users(id);

-- Para aspirantes_complementarios
ALTER TABLE aspirantes_complementarios
ADD COLUMN user_create_id INTEGER NULL AFTER documento_identidad_nombre,
ADD COLUMN user_edit_id INTEGER NULL AFTER user_create_id,
ADD FOREIGN KEY (user_create_id) REFERENCES users(id),
ADD FOREIGN KEY (user_edit_id) REFERENCES users(id);

-- Para persona_caracterizacion
ALTER TABLE persona_caracterizacion
ADD COLUMN user_create_id INTEGER NULL AFTER parametro_id,
ADD COLUMN user_edit_id INTEGER NULL AFTER user_create_id,
ADD FOREIGN KEY (user_create_id) REFERENCES users(id),
ADD FOREIGN KEY (user_edit_id) REFERENCES users(id);
```

### 2. Parametrizar campos hardcodeados
```sql
-- Crear parámetros para estados de programas complementarios
INSERT INTO temas (name, status) VALUES ('ESTADO_PROGRAMA_COMPLEMENTARIO', 1);

-- Insertar valores parametrizados
INSERT INTO parametros (name, status) VALUES
    ('Sin Oferta', 1),
    ('Con Oferta', 1),
    ('Cupos Llenos', 1);

-- Relacionar con tema
INSERT INTO parametros_temas (tema_id, parametro_id, status) VALUES
    (LAST_INSERT_ID() - 2, (SELECT id FROM parametros WHERE name = 'Sin Oferta'), 1),
    (LAST_INSERT_ID() - 2, (SELECT id FROM parametros WHERE name = 'Con Oferta'), 1),
    (LAST_INSERT_ID() - 2, (SELECT id FROM parametros WHERE name = 'Cupos Llenos'), 1);

-- Modificar tabla complementarios_ofertados para usar FK
ALTER TABLE complementarios_ofertados
DROP COLUMN estado,
ADD COLUMN estado_id INTEGER NULL AFTER cupos,
ADD FOREIGN KEY (estado_id) REFERENCES parametros_temas(id);
```

### 3. Parametrizar estados de aspirantes
```sql
-- Similar proceso para estados de aspirantes_complementarios
INSERT INTO temas (name, status) VALUES ('ESTADO_ASPIRANTE_COMPLEMENTARIO', 1);

INSERT INTO parametros (name, status) VALUES
    ('En proceso', 1),
    ('Admitido', 1),
    ('Rechazado', 1);

-- ... (relacionar con tema y modificar tabla)
```

## Diagrama de Relaciones Simplificado
```
personas ────┐
             ├── aspirantes_complementarios ─── complementarios_ofertados ─┬── parametros_temas (modalidad)
parametros ──┼── persona_caracterizacion       │                          ├── jornadas_formacion
             │                                  │                          └── ambientes
             └── (caracterización principal)    │
                                                └──┬── competencia_complementario ─── competencias
                                                   ├── resultado_aprendizaje_complementario ─── resultados_aprendizajes
                                                   ├── rap_competencia_complementario
                                                   └── guia_aprendizaje_complementario ─── guia_aprendizajes
```

## Notas Finales

1. **Consistencia**: El sistema actual muestra inconsistencia en el manejo de auditoría (algunas tablas tienen `user_create_id/user_edit_id`, otras no).
2. **Parametrización**: Los campos `estado` deberían ser FK a `parametros_temas` para mantener consistencia con el diseño del sistema.
3. **Mantenibilidad**: Las tablas de relación many-to-many tienen mejor diseño (incluyen auditoría completa).
4. **Migraciones pendientes**: Se requieren migraciones para corregir las deficiencias identificadas.

Este MER refleja el estado actual del sistema y proporciona recomendaciones para mejorarlo según los patrones establecidos en otras partes de la aplicación.
