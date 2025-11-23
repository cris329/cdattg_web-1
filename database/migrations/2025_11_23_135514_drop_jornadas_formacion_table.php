<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar foreign keys de jornadas_formacion a parametros_temas
        
        // 1. Cambiar foreign key de fichas_caracterizacion.jornada_id a parametros_temas
        if (Schema::hasTable('fichas_caracterizacion') && Schema::hasColumn('fichas_caracterizacion', 'jornada_id')) {
            // Eliminar foreign key antigua si existe
            try {
                DB::statement('ALTER TABLE fichas_caracterizacion DROP FOREIGN KEY fichas_caracterizacion_jornada_id_foreign');
            } catch (\Exception $e) {
                // Intentar con otro nombre posible
                try {
                    $constraints = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'fichas_caracterizacion' 
                        AND COLUMN_NAME = 'jornada_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    foreach ($constraints as $constraint) {
                        DB::statement("ALTER TABLE fichas_caracterizacion DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                    }
                } catch (\Exception $e2) {
                    // Continuar si no se puede eliminar
                }
            }
            
            // Agregar nueva foreign key a parametros_temas
            try {
                DB::statement('ALTER TABLE fichas_caracterizacion 
                    ADD CONSTRAINT fichas_caracterizacion_jornada_id_foreign 
                    FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                // Si falla, intentar sin nombre de constraint
                DB::statement('ALTER TABLE fichas_caracterizacion 
                    ADD FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            }
        }
        
        // 2. Cambiar foreign key de caracterizacion_programas.jornada_id a parametros_temas
        if (Schema::hasTable('caracterizacion_programas') && Schema::hasColumn('caracterizacion_programas', 'jornada_id')) {
            try {
                DB::statement('ALTER TABLE caracterizacion_programas DROP FOREIGN KEY caracterizacion_programas_jornada_id_foreign');
            } catch (\Exception $e) {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'caracterizacion_programas' 
                    AND COLUMN_NAME = 'jornada_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE caracterizacion_programas DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                }
            }
            
            try {
                DB::statement('ALTER TABLE caracterizacion_programas 
                    ADD CONSTRAINT caracterizacion_programas_jornada_id_foreign 
                    FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE caracterizacion_programas 
                    ADD FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            }
        }
        
        // 3. Cambiar foreign key de complementarios_ofertados.jornada_id a parametros_temas
        if (Schema::hasTable('complementarios_ofertados') && Schema::hasColumn('complementarios_ofertados', 'jornada_id')) {
            try {
                DB::statement('ALTER TABLE complementarios_ofertados DROP FOREIGN KEY complementarios_ofertados_jornada_id_foreign');
            } catch (\Exception $e) {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'complementarios_ofertados' 
                    AND COLUMN_NAME = 'jornada_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE complementarios_ofertados DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                }
            }
            
            try {
                DB::statement('ALTER TABLE complementarios_ofertados 
                    ADD CONSTRAINT complementarios_ofertados_jornada_id_foreign 
                    FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE complementarios_ofertados 
                    ADD FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            }
        }
        
        // 4. Cambiar foreign key de ambiente_instructor_ficha.jornada_id a parametros_temas
        if (Schema::hasTable('ambiente_instructor_ficha') && Schema::hasColumn('ambiente_instructor_ficha', 'jornada_id')) {
            try {
                DB::statement('ALTER TABLE ambiente_instructor_ficha DROP FOREIGN KEY ambiente_instructor_ficha_jornada_id_foreign');
            } catch (\Exception $e) {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'ambiente_instructor_ficha' 
                    AND COLUMN_NAME = 'jornada_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE ambiente_instructor_ficha DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                }
            }
            
            try {
                DB::statement('ALTER TABLE ambiente_instructor_ficha 
                    ADD CONSTRAINT ambiente_instructor_ficha_jornada_id_foreign 
                    FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE ambiente_instructor_ficha 
                    ADD FOREIGN KEY (jornada_id) REFERENCES parametros_temas(id) ON DELETE SET NULL');
            }
        }
        
        // 5. Cambiar tabla pivot instructor_jornada_formacion para usar parametros_temas
        if (Schema::hasTable('instructor_jornada_formacion')) {
            // Eliminar foreign key antigua
            try {
                DB::statement('ALTER TABLE instructor_jornada_formacion DROP FOREIGN KEY instructor_jornada_formacion_jornada_formacion_id_foreign');
            } catch (\Exception $e) {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'instructor_jornada_formacion' 
                    AND COLUMN_NAME = 'jornada_formacion_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE instructor_jornada_formacion DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                }
            }
            
            // Renombrar columna
            DB::statement('ALTER TABLE instructor_jornada_formacion CHANGE jornada_formacion_id parametro_tema_id BIGINT UNSIGNED');
            
            // Agregar nueva foreign key
            try {
                DB::statement('ALTER TABLE instructor_jornada_formacion 
                    ADD CONSTRAINT instructor_jornada_formacion_parametro_tema_id_foreign 
                    FOREIGN KEY (parametro_tema_id) REFERENCES parametros_temas(id) ON DELETE CASCADE');
            } catch (\Exception $e) {
                DB::statement('ALTER TABLE instructor_jornada_formacion 
                    ADD FOREIGN KEY (parametro_tema_id) REFERENCES parametros_temas(id) ON DELETE CASCADE');
            }
            
            // Renombrar tabla
            Schema::rename('instructor_jornada_formacion', 'instructor_parametro_tema');
        }
        
        // 6. Eliminar tabla jornadas_formacion
        if (Schema::hasTable('jornadas_formacion')) {
            Schema::dropIfExists('jornadas_formacion');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear tabla jornadas_formacion
        Schema::create('jornadas_formacion', function (Blueprint $table) {
            $table->id();
            $table->string('jornada');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->timestamps();
        });
        
        // Renombrar tabla pivot de vuelta
        if (Schema::hasTable('instructor_parametro_tema')) {
            Schema::rename('instructor_parametro_tema', 'instructor_jornada_formacion');
            
            // Eliminar foreign key a parametros_temas
            try {
                DB::statement('ALTER TABLE instructor_jornada_formacion DROP FOREIGN KEY instructor_jornada_formacion_parametro_tema_id_foreign');
            } catch (\Exception $e) {
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'instructor_jornada_formacion' 
                    AND COLUMN_NAME = 'parametro_tema_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE instructor_jornada_formacion DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                }
            }
            
            // Cambiar columna de vuelta
            DB::statement('ALTER TABLE instructor_jornada_formacion CHANGE parametro_tema_id jornada_formacion_id BIGINT UNSIGNED');
            
            // Agregar foreign key antigua
            Schema::table('instructor_jornada_formacion', function (Blueprint $table) {
                $table->foreign('jornada_formacion_id')
                    ->references('id')
                    ->on('jornadas_formacion')
                    ->onDelete('cascade');
            });
        }
        
        // Recrear foreign keys (solo si las tablas existen)
        if (Schema::hasTable('fichas_caracterizacion')) {
            Schema::table('fichas_caracterizacion', function (Blueprint $table) {
                if (Schema::hasColumn('fichas_caracterizacion', 'jornada_id')) {
                    try {
                        DB::statement('ALTER TABLE fichas_caracterizacion DROP FOREIGN KEY fichas_caracterizacion_jornada_id_foreign');
                    } catch (\Exception $e) {
                        // Continuar
                    }
                    $table->foreign('jornada_id')->references('id')->on('jornadas_formacion')->onDelete('set null');
                }
            });
        }
        
        if (Schema::hasTable('caracterizacion_programas')) {
            Schema::table('caracterizacion_programas', function (Blueprint $table) {
                if (Schema::hasColumn('caracterizacion_programas', 'jornada_id')) {
                    try {
                        DB::statement('ALTER TABLE caracterizacion_programas DROP FOREIGN KEY caracterizacion_programas_jornada_id_foreign');
                    } catch (\Exception $e) {
                        // Continuar
                    }
                    $table->foreign('jornada_id')->references('id')->on('jornadas_formacion')->onDelete('set null');
                }
            });
        }
        
        if (Schema::hasTable('complementarios_ofertados')) {
            Schema::table('complementarios_ofertados', function (Blueprint $table) {
                if (Schema::hasColumn('complementarios_ofertados', 'jornada_id')) {
                    try {
                        DB::statement('ALTER TABLE complementarios_ofertados DROP FOREIGN KEY complementarios_ofertados_jornada_id_foreign');
                    } catch (\Exception $e) {
                        // Continuar
                    }
                    $table->foreign('jornada_id')->references('id')->on('jornadas_formacion')->onDelete('set null');
                }
            });
        }
        
        if (Schema::hasTable('ambiente_instructor_ficha')) {
            Schema::table('ambiente_instructor_ficha', function (Blueprint $table) {
                if (Schema::hasColumn('ambiente_instructor_ficha', 'jornada_id')) {
                    try {
                        DB::statement('ALTER TABLE ambiente_instructor_ficha DROP FOREIGN KEY ambiente_instructor_ficha_jornada_id_foreign');
                    } catch (\Exception $e) {
                        // Continuar
                    }
                    $table->foreign('jornada_id')->references('id')->on('jornadas_formacion')->onDelete('set null');
                }
            });
        }
    }
};
