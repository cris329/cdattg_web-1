<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Instructor::class;

    public function definition(): array
    {
        $regionalId = Regional::query()->inRandomOrder()->value('id') ?? 1;
        
        // Obtener IDs de RedConocimiento para especialidades
        $redesConocimiento = \App\Models\RedConocimiento::query()->inRandomOrder()->get();
        $principalId = $redesConocimiento->first()?->id ?? null;
        $secundariasIds = $redesConocimiento->skip(1)->take(rand(0, 2))->pluck('id')->toArray();

        $competencias = [
            'Programación Web',
            'Bases de Datos',
            'Automatización Industrial',
            'Gestión de Proyectos',
            'Diseño Gráfico',
            'Seguridad Informática',
            'Analítica de Datos',
            'Redes de Computadores',
        ];

        $competenciasSeleccionadas = Collection::make($competencias)
            ->shuffle()
            ->take(rand(2, 4))
            ->values()
            ->all();

        // Obtener IDs para campos relacionados
        // tipo_vinculacion_id apunta a parametros_temas
        $tipoVinculacionId = \App\Models\ParametroTema::whereHas('tema', function($q) {
            $q->where('name', 'like', '%VINCULACION%');
        })->inRandomOrder()->value('id');
        
        // nivel_academico_id apunta a parametros (según la foreign key real en la BD)
        $nivelAcademicoId = \App\Models\Parametro::whereHas('temas', function($q) {
            $q->where('name', 'like', '%NIVEL%ACADEMICO%');
        })->inRandomOrder()->value('id');
        
        // Si no encuentra ninguno, usar null (el campo es nullable) o un valor aleatorio
        if (!$tipoVinculacionId) {
            $tipoVinculacionId = \App\Models\ParametroTema::inRandomOrder()->value('id');
        }
        if (!$nivelAcademicoId) {
            $nivelAcademicoId = \App\Models\Parametro::inRandomOrder()->value('id');
        }

        $centroFormacionId = \App\Models\CentroFormacion::query()->inRandomOrder()->value('id');

        $idiomas = [
            ['idioma' => 'Inglés', 'nivel' => 'intermedio'],
            ['idioma' => 'Francés', 'nivel' => 'básico'],
        ];

        return [
            'persona_id' => Persona::factory(),
            'regional_id' => $regionalId,
            'status' => (rand(1, 100) <= 85) ? 1 : 0,
            'user_create_id' => 1,
            'user_edit_id' => 1,
            'especialidades' => [
                'principal' => $principalId,
                'secundarias' => $secundariasIds,
            ],
            'competencias' => $competenciasSeleccionadas,
            'anos_experiencia' => rand(2, 25),
            'experiencia_laboral' => 'Experiencia profesional con múltiples proyectos desarrollados.',
            'tipo_vinculacion_id' => $tipoVinculacionId,
            'centro_formacion_id' => $centroFormacionId,
            'experiencia_instructor_meses' => rand(6, 120),
            'fecha_ingreso_sena' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'nivel_academico_id' => $nivelAcademicoId,
            'titulos_obtenidos' => ['Técnico', 'Tecnólogo', 'Profesional'],
            'instituciones_educativas' => ['SENA', 'Universidad Nacional', 'Universidad de los Andes'],
            'certificaciones_tecnicas' => ['Certificación en ' . $this->faker->word],
            'cursos_complementarios' => ['Curso de ' . $this->faker->word],
            'formacion_pedagogia' => 'Diplomado en pedagogía SENA',
            'areas_experticia' => $competenciasSeleccionadas,
            'competencias_tic' => ['Office', 'Programación', 'Bases de Datos'],
            'idiomas' => array_slice($idiomas, 0, rand(1, 2)),
            'habilidades_pedagogicas' => ['presencial', 'virtual'],
            'numero_contrato' => 'CT-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            'fecha_inicio_contrato' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'fecha_fin_contrato' => $this->faker->dateTimeBetween('now', '+2 years'),
            'supervisor_contrato' => $this->faker->name,
            'eps' => $this->faker->company,
            'arl' => $this->faker->company,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Instructor $instructor) {
            $persona = $instructor->persona;

            if (! $persona) {
                return;
            }

            $email = strtolower($persona->email);

            if (! $persona->user) {
                $user = User::factory()
                    ->forPersona($persona)
                    ->state([
                        'email' => $email,
                        'status' => $instructor->status ? 1 : 0,
                    ])
                    ->create();

                if (! $user->hasRole('INSTRUCTOR')) {
                    $user->assignRole('INSTRUCTOR');
                }
            } else {
                $persona->user->syncRoles(['INSTRUCTOR']);
                $persona->user->update(['status' => $instructor->status ? 1 : 0]);
            }
        });
    }

    public function createdBy(int $userId): static
    {
        return $this->state(fn () => [
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ]);
    }
}
