<?php

namespace Database\Seeders;

use App\Models\Aprendiz;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\InstructorFichaCaracterizacion;
use App\Models\InstructorFichaDias;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use App\Models\Regional;
use App\Models\ResultadosAprendizaje;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class TestingSeeder extends Seeder
{
    private $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create('es_ES');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creando datos de prueba...');

        // Crear redes de conocimiento
        $this->command->info('🌐 Creando redes de conocimiento...');
        $redesConocimiento = [];
        $regionalId = Regional::query()->inRandomOrder()->value('id') ?? 1;
        
        $nombresRedes = [
            'Tecnologías de la Información y las Comunicaciones',
            'Electrónica',
            'Mecánica Industrial',
            'Construcción',
            'Gastronomía',
            'Diseño',
            'Gestión Empresarial',
            'Salud',
            'Agropecuaria',
            'Turismo',
        ];
        
        // Mezclar y tomar nombres únicos
        $nombresDisponibles = collect($nombresRedes)->shuffle();
        $nombresUsados = [];
        
        for ($i = 0; $i < 5; $i++) {
            // Buscar un nombre que no exista en la BD y no haya sido usado
            $nombre = null;
            $intentos = 0;
            while ($intentos < 20) {
                $candidato = $nombresDisponibles->random();
                if (!in_array($candidato, $nombresUsados) && 
                    !RedConocimiento::where('nombre', $candidato)->exists()) {
                    $nombre = $candidato;
                    break;
                }
                $intentos++;
            }
            
            // Si no se encontró uno único, agregar un sufijo único
            if (!$nombre) {
                $baseNombre = $nombresDisponibles->random();
                $sufijo = rand(1000, 9999);
                $nombre = $baseNombre . ' ' . $sufijo;
            }
            
            $nombresUsados[] = $nombre;
            
            $redesConocimiento[] = RedConocimiento::create([
                'nombre' => $nombre,
                'regionals_id' => $regionalId,
                'status' => 1,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]);
        }
        $this->command->info("✓ Creadas " . count($redesConocimiento) . " redes de conocimiento");

        // Crear programas de formación
        $this->command->info('📘 Creando programas de formación...');
        $programasFormacion = [];
        $nivelFormacionId = \App\Models\Parametro::whereIn('name', ['TÉCNICO', 'TECNÓLOGO', 'AUXILIAR', 'OPERARIO'])
            ->inRandomOrder()
            ->value('id') ?? \App\Models\Parametro::inRandomOrder()->value('id');
        
        $nombresProgramas = [
            'Tecnología en Desarrollo de Software',
            'Tecnología en Redes de Computadores',
            'Técnico en Programación',
            'Tecnología en Automatización Industrial',
            'Técnico en Construcción',
            'Tecnología en Gastronomía',
            'Técnico en Diseño Gráfico',
        ];
        
        foreach ($redesConocimiento as $red) {
            $numProgramas = rand(1, 2);
            for ($j = 0; $j < $numProgramas; $j++) {
                $horasTotales = rand(800, 2200);
                $horasEtapaLectiva = rand(400, $horasTotales - 200);
                $horasEtapaProductiva = $horasTotales - $horasEtapaLectiva;
                
                $programasFormacion[] = ProgramaFormacion::create([
                    'codigo' => (string) rand(100000, 999999),
                    'nombre' => $this->faker->randomElement($nombresProgramas),
                    'red_conocimiento_id' => $red->id,
                    'nivel_formacion_id' => $nivelFormacionId,
                    'horas_totales' => $horasTotales,
                    'horas_etapa_lectiva' => $horasEtapaLectiva,
                    'horas_etapa_productiva' => $horasEtapaProductiva,
                    'status' => 1,
                    'user_create_id' => 1,
                    'user_edit_id' => 1,
                ]);
            }
        }
        $this->command->info("✓ Creados " . count($programasFormacion) . " programas de formación");

        // Crear competencias
        $this->command->info('📚 Creando competencias...');
        $competencias = [];
        for ($i = 0; $i < 5; $i++) {
            $competencias[] = Competencia::create([
                'codigo' => 'COMP-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'nombre' => $this->faker->randomElement([
                    'Desarrollo de Software',
                    'Administración de Bases de Datos',
                    'Redes de Computadores',
                    'Seguridad Informática',
                    'Automatización Industrial',
                ]),
                'descripcion' => $this->faker->paragraph(3),
                'duracion' => rand(40, 200),
                'fecha_inicio' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'fecha_fin' => $this->faker->dateTimeBetween('now', '+2 years'),
                'status' => 1,
                'user_create_id' => 1,
                'user_edit_id' => 1,
            ]);
        }
        $this->command->info("✓ Creadas " . count($competencias) . " competencias");

        // Crear resultados de aprendizaje y asociarlos con competencias
        $this->command->info('📖 Creando resultados de aprendizaje...');
        $totalResultados = 0;
        foreach ($competencias as $competencia) {
            $numResultados = rand(3, 5);
            $duracionPorResultado = $competencia->duracion / $numResultados;
            
            for ($j = 0; $j < $numResultados; $j++) {
                $resultado = ResultadosAprendizaje::create([
                    'codigo' => 'RAP-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'nombre' => $this->faker->randomElement([
                        'Aplicar metodologías de desarrollo de software',
                        'Implementar bases de datos relacionales',
                        'Configurar redes de área local',
                        'Aplicar medidas de seguridad informática',
                        'Operar sistemas automatizados',
                    ]),
                    'duracion' => $duracionPorResultado,
                    'status' => 1,
                    'user_create_id' => 1,
                    'user_edit_id' => 1,
                ]);
                
                // Asociar resultado con competencia
                $competencia->resultadosAprendizaje()->attach($resultado->id, [
                    'duracion' => $duracionPorResultado
                ]);
                
                $totalResultados++;
            }
        }
        $this->command->info("✓ Creados {$totalResultados} resultados de aprendizaje");

        // Asignar competencias a programas de formación
        $this->command->info('🔗 Asignando competencias a programas de formación...');
        $asignacionesCompetencias = 0;
        foreach ($programasFormacion as $programa) {
            $numCompetencias = rand(2, 4);
            $competenciasParaPrograma = collect($competencias)->random(
                min($numCompetencias, count($competencias))
            );
            
            foreach ($competenciasParaPrograma as $competencia) {
                $programa->competencias()->attach($competencia->id, [
                    'user_create_id' => 1,
                    'user_edit_id' => 1,
                ]);
                $asignacionesCompetencias++;
            }
        }
        $this->command->info("✓ Asignadas {$asignacionesCompetencias} competencias a programas de formación");

        // Crear fichas de caracterización y asignarles programas de formación
        $this->command->info('📋 Creando fichas de caracterización...');
        $fichas = [];
        $numerosFichasUsados = [];
        
        foreach ($programasFormacion as $programa) {
            $numFichas = rand(1, 3);
            for ($k = 0; $k < $numFichas; $k++) {
                // Generar número de ficha único
                $numeroFicha = null;
                $intentos = 0;
                while ($intentos < 100) {
                    $candidato = 'FICHA-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $key = $programa->id . '_' . $candidato;
                    if (!isset($numerosFichasUsados[$key]) && 
                        !FichaCaracterizacion::where('ficha', $candidato)
                            ->where('programa_formacion_id', $programa->id)->exists() &&
                        !FichaCaracterizacion::where('ficha', $candidato)->exists()) {
                        $numeroFicha = $candidato;
                        $numerosFichasUsados[$key] = true;
                        break;
                    }
                    $intentos++;
                }
                
                if (!$numeroFicha) {
                    $numeroFicha = 'FICHA-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . '_' . time() . '_' . rand(1000, 9999);
                }
                
                // Validar fechas: fecha_inicio >= hace 2 años, fecha_fin > fecha_inicio
                // No fines de semana, duración mínima de 30 días
                $fechaInicio = \Carbon\Carbon::now()->subMonths(rand(0, 6));
                // Asegurar que no sea fin de semana
                while ($fechaInicio->isWeekend()) {
                    $fechaInicio->addDay();
                }
                
                // Duración mínima de 30 días
                $diasDuracion = rand(30, 180);
                $fechaFin = (clone $fechaInicio)->addDays($diasDuracion);
                // Asegurar que no sea fin de semana
                while ($fechaFin->isWeekend()) {
                    $fechaFin->addDay();
                }
                
                // Asegurar que fecha_inicio >= hace 2 años
                $fechaMinima = \Carbon\Carbon::now()->subYears(2);
                if ($fechaInicio->lt($fechaMinima)) {
                    $fechaInicio = clone $fechaMinima;
                    while ($fechaInicio->isWeekend()) {
                        $fechaInicio->addDay();
                    }
                    $fechaFin = (clone $fechaInicio)->addDays($diasDuracion);
                    while ($fechaFin->isWeekend()) {
                        $fechaFin->addDay();
                    }
                }
                
                // Obtener IDs válidos para relaciones
                $sedeId = \App\Models\Sede::inRandomOrder()->value('id');
                $ambienteId = \App\Models\Ambiente::inRandomOrder()->value('id');
                $instructorId = \App\Models\Instructor::inRandomOrder()->value('id');
                
                // Obtener jornada desde parametros_temas del tema JORNADAS
                $jornadaId = \App\Models\ParametroTema::whereHas('tema', function($q) {
                    $q->where('name', 'LIKE', '%JORNADAS%');
                })->inRandomOrder()->value('id');
                
                $fichaData = [
                    'programa_formacion_id' => $programa->id,
                    'ficha' => $numeroFicha,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                ];
                
                // Solo agregar relaciones si existen
                if ($sedeId) {
                    $fichaData['sede_id'] = $sedeId;
                }
                if ($ambienteId) {
                    $fichaData['ambiente_id'] = $ambienteId;
                }
                if ($instructorId) {
                    $fichaData['instructor_id'] = $instructorId;
                }
                if ($jornadaId) {
                    $fichaData['jornada_id'] = $jornadaId;
                }
                
                $fichas[] = FichaCaracterizacion::factory()
                    ->state($fichaData)
                    ->create();
            }
        }
        $this->command->info("✓ Creadas " . count($fichas) . " fichas de caracterización");

        // Asignar días de formación a las fichas
        $this->command->info('📅 Asignando días de formación a las fichas...');
        $diasFormacionCreados = 0;
        foreach ($fichas as $ficha) {
            // Seleccionar 3-5 días de la semana (Lunes a Viernes = 12-16)
            $diasSemana = [12, 13, 14, 15, 16];
            $diasSeleccionados = collect($diasSemana)->random(rand(3, 5));
            
            foreach ($diasSeleccionados as $diaId) {
                // Usar horarios por defecto (las jornadas ahora están en tema-parametro)
                $horaInicio = '08:00:00';
                $horaFin = '12:00:00';
                
                \App\Models\FichaDiasFormacion::create([
                    'ficha_id' => $ficha->id,
                    'dia_id' => $diaId,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                ]);
                $diasFormacionCreados++;
            }
        }
        $this->command->info("✓ Creados {$diasFormacionCreados} días de formación para las fichas");

        // Crear instructores
        $this->command->info('👨‍🏫 Creando instructores...');
        $instructores = Instructor::factory()
            ->count(15)
            ->create();
        $this->command->info("✓ Creados {$instructores->count()} instructores");

        // Asignar instructores a fichas usando el servicio con todas las validaciones
        $this->command->info('🔗 Asignando instructores a fichas (con validaciones)...');
        $asignacionesCreadas = 0;
        $asignacionService = app(\App\Services\AsignacionInstructorService::class);
        
        foreach ($fichas as $ficha) {
            // Recargar ficha con relaciones necesarias
            $ficha->load(['programaFormacion.redConocimiento', 'diasFormacion']);
            
            // Obtener competencias del programa de formación de la ficha
            $competenciasDelPrograma = $ficha->programaFormacion->competencias;
            if ($competenciasDelPrograma->isEmpty()) {
                continue; // Saltar si no hay competencias en el programa
            }
            
            $numInstructores = rand(1, 2); // Reducir para evitar conflictos
            $instructoresParaFicha = $instructores->random(min($numInstructores, $instructores->count()));

            $instructoresData = [];
            foreach ($instructoresParaFicha as $instructor) {
                // Seleccionar una competencia del programa
                $competencia = $competenciasDelPrograma->random();
                $resultadosCompetencia = $competencia->resultadosAprendizaje->random(
                    min(rand(2, $competencia->resultadosAprendizaje->count()), $competencia->resultadosAprendizaje->count())
                );

                // Obtener días de formación de la ficha
                $diasFicha = $ficha->diasFormacion->pluck('dia_id')->toArray();
                if (empty($diasFicha)) {
                    continue; // Saltar si la ficha no tiene días configurados
                }
                
                // Seleccionar algunos días de los configurados en la ficha
                $diasSeleccionados = collect($diasFicha)->random(min(rand(3, count($diasFicha)), count($diasFicha)));

                // Crear fechas dentro del rango de la ficha
                $fechaInicioFicha = \Carbon\Carbon::parse($ficha->fecha_inicio);
                $fechaFinFicha = \Carbon\Carbon::parse($ficha->fecha_fin);
                
                // Fecha inicio del instructor (dentro del rango de la ficha)
                $diasDesdeInicio = rand(0, min(30, $fechaInicioFicha->diffInDays($fechaFinFicha)));
                $fechaInicio = (clone $fechaInicioFicha)->addDays($diasDesdeInicio);
                
                // Fecha fin del instructor (no más allá de la ficha)
                $diasDuracion = rand(60, min(180, $fechaInicio->diffInDays($fechaFinFicha)));
                $fechaFin = (clone $fechaInicio)->addDays($diasDuracion);
                
                // Asegurar que no exceda la fecha fin de la ficha
                if ($fechaFin->gt($fechaFinFicha)) {
                    $fechaFin = clone $fechaFinFicha;
                }

                // Preparar datos del instructor en el formato que espera el servicio
                $instructorData = [
                    'instructor_id' => $instructor->id,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                    'competencia_id' => $competencia->id,
                    'resultados_aprendizaje' => $resultadosCompetencia->pluck('id')->toArray(),
                    'dias_semana' => $diasSeleccionados->toArray(),
                ];

                $instructoresData[] = $instructorData;
            }

            if (!empty($instructoresData)) {
                try {
                    // Usar el servicio de asignación que incluye todas las validaciones
                    $resultado = $asignacionService->asignarInstructores(
                        $instructoresData,
                        $ficha->id,
                        $ficha->instructor_id ?? $instructoresData[0]['instructor_id'], // Instructor principal
                        1 // user_id
                    );

                    if ($resultado['success']) {
                        $asignacionesCreadas += count($instructoresData);
                    } else {
                        $this->command->warn("⚠ No se pudieron asignar instructores a la ficha {$ficha->id}: " . $resultado['message']);
                    }
                } catch (\Exception $e) {
                    $this->command->warn("⚠ Error al asignar instructores a la ficha {$ficha->id}: " . $e->getMessage());
                }
            }
        }
        $this->command->info("✓ Creadas {$asignacionesCreadas} asignaciones de instructores");

        // Crear aprendices y asignarlos a fichas
        $this->command->info('👨‍🎓 Creando aprendices y asignándolos a fichas...');
        $aprendicesCreados = 0;
        $contadorEmail = 0;
        foreach ($fichas as $ficha) {
            $numAprendices = rand(10, 30);
            for ($k = 0; $k < $numAprendices; $k++) {
                $contadorEmail++;
                // Crear persona con email único
                $persona = \App\Models\Persona::factory()->create([
                    'email' => 'aprendiz' . $contadorEmail . '_' . time() . '_' . rand(1000, 9999) . '@example.com',
                ]);
                
                // Crear aprendiz
                Aprendiz::create([
                    'persona_id' => $persona->id,
                    'ficha_caracterizacion_id' => $ficha->id,
                    'estado' => 1,
                    'user_create_id' => 1,
                    'user_edit_id' => 1,
                ]);
                
                // Asignar rol APRENDIZ si tiene usuario
                if ($persona->user && !$persona->user->hasRole('APRENDIZ')) {
                    $persona->user->assignRole('APRENDIZ');
                }
                
                $aprendicesCreados++;
            }
        }

        $this->command->info("✓ Creados {$aprendicesCreados} aprendices asignados a fichas");

        // Resumen
        $this->command->info('');
        $this->command->info('✅ Datos de prueba creados exitosamente:');
        $this->command->info("   - " . count($redesConocimiento) . " redes de conocimiento");
        $this->command->info("   - " . count($programasFormacion) . " programas de formación");
        $this->command->info("   - {$asignacionesCompetencias} asignaciones de competencias a programas");
        $this->command->info("   - " . count($competencias) . " competencias");
        $this->command->info("   - {$totalResultados} resultados de aprendizaje");
        $this->command->info("   - " . count($fichas) . " fichas de caracterización");
        $this->command->info("   - {$diasFormacionCreados} días de formación asignados a fichas");
        $this->command->info("   - {$instructores->count()} instructores");
        $this->command->info("   - {$asignacionesCreadas} asignaciones de instructores (con validaciones)");
        $this->command->info("   - {$aprendicesCreados} aprendices");
    }
}

