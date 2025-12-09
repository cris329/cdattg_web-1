<?php

namespace Database\Factories;

use App\Models\Ambiente;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\JornadaFormacion;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use App\Models\Regional;
use App\Models\Sede;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FichaCaracterizacion>
 */
class FichaCaracterizacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = FichaCaracterizacion::class;

    public function definition(): array
    {
        return [
            'programa_formacion_id' => $this->obtenerProgramaId(),
            'ficha' => $this->generarNumeroFicha(),
            'instructor_id' => Instructor::factory(),
            'fecha_inicio' => $this->generarFechaInicio(),
            'fecha_fin' => $this->generarFechaFin(),
            'ambiente_id' => $this->obtenerAmbienteId(),
            'modalidad_formacion_id' => $this->obtenerModalidadId(),
            'sede_id' => $this->obtenerSedeId(),
            'jornada_id' => $this->obtenerJornadaId(),
            'total_horas' => rand(1200, 3200),
            'user_create_id' => $this->obtenerUserId(),
            'user_edit_id' => $this->obtenerUserId(),
            'status' => (rand(1, 100) <= 90) ? 1 : 0,
        ];
    }

    /**
     * Obtiene o crea un ID de programa de formación
     */
    private function obtenerProgramaId(): ?int
    {
        if (!Schema::hasTable('programas_formacion')) {
            return null;
        }

        try {
            $programaId = ProgramaFormacion::query()->inRandomOrder()->value('id');
            if ($programaId) {
                return $programaId;
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        // Crear dependencias y programa directamente
        $user = $this->obtenerOcrearUsuario();
        $redConocimiento = $this->obtenerOcrearRedConocimiento($user);
        $nivelFormacionParametroTema = $this->obtenerOcrearNivelFormacionParametroTema($user);

        // Crear el programa directamente con los IDs verificados
        if (! $nivelFormacionParametroTema) {
            // Si no se pudo crear el parametro_tema, lanzar error
            throw new \RuntimeException('No se pudo crear o encontrar un parametro_tema para nivel de formación');
        }

        $programa = ProgramaFormacion::query()->create([
            'codigo' => (string) rand(100000, 999999),
            'nombre' => $this->faker->unique()->sentence(3),
            'red_conocimiento_id' => $redConocimiento->id,
            'nivel_formacion_id' => $nivelFormacionParametroTema->id,
            'horas_totales' => 1200,
            'horas_etapa_lectiva' => 800,
            'horas_etapa_productiva' => 400,
            'status' => true,
            'user_create_id' => $user->id,
            'user_edit_id' => $user->id,
        ]);
        
        return $programa->id;
    }

    /**
     * Obtiene el ID de parametros_temas basado en tema_id y parametro_id
     */
    private function getParametroTemaId(int $temaId, int $parametroId): ?int
    {
        if (! Schema::hasTable('parametros_temas')) {
            return null;
        }

        try {
            $parametroTema = \Illuminate\Support\Facades\DB::table('parametros_temas')
                ->where('tema_id', $temaId)
                ->where('parametro_id', $parametroId)
                ->first();

            return $parametroTema?->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Genera un número de ficha aleatorio
     */
    private function generarNumeroFicha(): string
    {
        $prefijoFicha = $this->faker->numberBetween(10, 99);
        $numeroFicha = str_pad($this->faker->numberBetween(10000, 99999), 5, '0', STR_PAD_LEFT);
        return $prefijoFicha . $numeroFicha;
    }

    /**
     * Genera una fecha de inicio aleatoria
     */
    private function generarFechaInicio(): string
    {
        $mesesAtras = rand(0, 6);
        $mesesAdelante = rand(0, 2);
        return date('Y-m-d', strtotime("-{$mesesAtras} months +{$mesesAdelante} months"));
    }

    /**
     * Genera una fecha de fin basada en la fecha de inicio
     */
    private function generarFechaFin(): string
    {
        $fechaInicio = $this->generarFechaInicio();
        $duracionMeses = rand(12, 24);
        return date('Y-m-d', strtotime($fechaInicio . " +{$duracionMeses} months"));
    }

    /**
     * Obtiene un ID de ambiente (puede ser null)
     */
    private function obtenerAmbienteId(): ?int
    {
        return $this->obtenerOcrearId(
            'ambientes',
            fn() => Ambiente::query()->inRandomOrder()->value('id'),
            fn() => Ambiente::factory()->create()->id
        );
    }

    /**
     * Obtiene un ID de modalidad (ParametroTema)
     */
    private function obtenerModalidadId(): ?int
    {
        $modalidadParametroIds = [18, 19, 20]; // PRESENCIAL, VIRTUAL, MIXTA
        $modalidadParametroId = $modalidadParametroIds[array_rand($modalidadParametroIds)];
        return $this->getParametroTemaId(5, $modalidadParametroId); // Tema: MODALIDADES DE FORMACION (5)
    }

    /**
     * Obtiene un ID de sede (puede ser null)
     */
    private function obtenerSedeId(): ?int
    {
        return $this->obtenerOcrearId(
            'sedes',
            fn() => Sede::query()->inRandomOrder()->value('id'),
            fn() => Sede::factory()->create()->id
        );
    }

    /**
     * Obtiene un ID de jornada de formación (puede ser null)
     */
    private function obtenerJornadaId(): ?int
    {
        return $this->obtenerOcrearId(
            'jornadas_formacion',
            fn() => JornadaFormacion::query()->inRandomOrder()->value('id'),
            fn() => JornadaFormacion::factory()->create()->id
        );
    }

    /**
     * Helper genérico para obtener o crear un ID
     */
    private function obtenerOcrearId(string $tableName, callable $obtenerFn, callable $crearFn): ?int
    {
        if (!Schema::hasTable($tableName)) {
            return null;
        }

        $result = null;
        
        try {
            $id = $obtenerFn();
            if ($id) {
                $result = $id;
            } else {
                $result = $crearFn();
            }
        } catch (\Exception $e) {
            try {
                $result = $crearFn();
            } catch (\Exception $e2) {
                $result = null;
            }
        }
        
        return $result;
    }

    /**
     * Obtiene un ID de usuario para user_create_id y user_edit_id
     */
    private function obtenerUserId(): int
    {
        if (!Schema::hasTable('users')) {
            return 1;
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
            return $userId ?: User::factory()->create()->id;
        } catch (\Exception $e) {
            return User::factory()->create()->id;
        }
    }

    /**
     * Obtiene o crea un usuario
     */
    private function obtenerOcrearUsuario(): User
    {
        $user = User::query()->inRandomOrder()->first();
        return $user ?: User::factory()->create();
    }

    /**
     * Obtiene o crea una red de conocimiento
     */
    private function obtenerOcrearRedConocimiento(User $user): RedConocimiento
    {
        $redConocimiento = RedConocimiento::query()->inRandomOrder()->first();
        if ($redConocimiento) {
            return $redConocimiento;
        }

        $regional = Regional::query()->inRandomOrder()->first();
        if (! $regional) {
            $regional = Regional::factory()->create();
        }

        return RedConocimiento::factory()->create([
            'regionals_id' => $regional->id,
            'user_create_id' => $user->id,
            'user_edit_id' => $user->id,
        ]);
    }

    /**
     * Obtiene o crea un parametro_tema para nivel de formación
     */
    private function obtenerOcrearNivelFormacionParametroTema(User $user): ?ParametroTema
    {
        if (!Schema::hasTable('parametros_temas') || !Schema::hasTable('temas') || !Schema::hasTable('parametros')) {
            return null;
        }

        // Buscar o crear el tema "NIVELES DE FORMACION"
        $temaNiveles = Tema::query()->where('name', 'NIVELES DE FORMACION')->first();
        if (! $temaNiveles) {
            $temaNiveles = Tema::query()->create([
                'name' => 'NIVELES DE FORMACION',
                'status' => 1,
                'user_create_id' => $user->id,
                'user_edit_id' => $user->id,
            ]);
        }

        // Buscar o crear un parámetro de nivel de formación
        $parametroNivel = Parametro::query()
            ->whereIn('name', ['TÉCNICO', 'TECNÓLOGO', 'AUXILIAR', 'OPERARIO'])
            ->first();
        if (! $parametroNivel) {
            $parametroNivel = Parametro::query()->create([
                'name' => 'TÉCNICO',
                'status' => 1,
                'user_create_id' => $user->id,
                'user_edit_id' => $user->id,
            ]);
        }

        // Buscar o crear el parametro_tema
        $nivelFormacionParametroTema = ParametroTema::query()
            ->where('tema_id', $temaNiveles->id)
            ->where('parametro_id', $parametroNivel->id)
            ->first();

        if (! $nivelFormacionParametroTema) {
            $nivelFormacionParametroTema = ParametroTema::query()->create([
                'tema_id' => $temaNiveles->id,
                'parametro_id' => $parametroNivel->id,
                'status' => 1,
                'user_create_id' => $user->id,
                'user_edit_id' => $user->id,
            ]);
        }

        return $nivelFormacionParametroTema;
    }
}
