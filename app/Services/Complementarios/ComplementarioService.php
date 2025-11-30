<?php

namespace App\Services\Complementarios;

use App\Exceptions\ProgramaNoEncontradoException;
use App\Models\Ambiente;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\TemaRepository;
use Illuminate\Database\Eloquent\Collection;

class ComplementarioService
{
    public function __construct(
        private readonly TemaRepository $temaRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly AspiranteComplementarioRepository $aspiranteRepository
    ) {}
    /**
     * Obtener icono para un programa complementario
     */
    public function getIconoForPrograma($nombre)
    {
        $iconos = [
            'Auxiliar de Cocina' => 'fas fa-utensils',
            'Acabados en Madera' => 'fas fa-hammer',
            'Confección de Prendas' => 'fas fa-cut',
            'Mecánica Básica Automotriz' => 'fas fa-car',
            'Cultivos de Huertas Urbanas' => 'fas fa-spa',
            'Normatividad Laboral' => 'fas fa-gavel',
        ];

        return $iconos[$nombre] ?? 'fas fa-graduation-cap';
    }

    /**
     * Obtener clase CSS para el badge según el estado del programa
     */
    public function getBadgeClassForEstado($estado)
    {
        $badgeClasses = [
            0 => 'bg-secondary', // Sin Oferta
            1 => 'bg-success',   // Con Oferta
            2 => 'bg-warning',   // Cupos Llenos
        ];

        return $badgeClasses[$estado] ?? 'bg-secondary';
    }

    /**
     * Obtener label del estado del programa
     */
    public function getEstadoLabel($estado)
    {
        $estados = [
            0 => 'Sin Oferta',
            1 => 'Con Oferta',
            2 => 'Cupos Llenos',
        ];

        return $estados[$estado] ?? 'Desconocido';
    }

    /**
     * Enriquecer un programa con datos auxiliares para la vista.
     */
    public function enriquecerPrograma(ComplementarioOfertado $programa): ComplementarioOfertado
    {
        $programa->icono = $this->getIconoForPrograma($programa->nombre);
        $programa->badge_class = $this->getBadgeClassForEstado($programa->estado);
        $programa->estado_label = $this->getEstadoLabel($programa->estado);
        $programa->modalidad_nombre = $programa->modalidad->parametro->name ?? null;
        $programa->jornada_nombre = $programa->jornada->jornada ?? null;

        return $programa;
    }

    /**
     * Enriquecer una colección de programas complementarios.
     */
    public function enriquecerProgramas(Collection $programas): Collection
    {
        return $programas->map(function (ComplementarioOfertado $programa) {
            return $this->enriquecerPrograma($programa);
        });
    }

    /**
     * Obtener programas con relaciones necesarias para diferentes vistas.
     */
    public function obtenerProgramas(array $relations = [], ?int $estado = null): Collection
    {
        if (!is_null($estado)) {
            return $this->programaRepository->getByEstado($estado, $relations);
        }

        return $this->programaRepository->getAll($relations);
    }

    /**
     * Sincronizar días de formación garantizando atomicidad.
     */
    public function sincronizarDiasFormacion(ComplementarioOfertado $programa, ?array $dias): void
    {
        if (empty($dias)) {
            $programa->diasFormacion()->detach();
            return;
        }

        $programa->diasFormacion()->sync(
            collect($dias)->mapWithKeys(static function ($dia) {
                return [
                    $dia['dia_id'] => [
                        'hora_inicio' => $dia['hora_inicio'],
                        'hora_fin' => $dia['hora_fin'],
                    ],
                ];
            })->all()
        );
    }

    /**
     * Datos compartidos para vistas de gestión/admin.
     */
    public function obtenerDatosFormulario(): array
    {
        $modalidades = ParametroTema::query()
            ->where('tema_id', 5)
            ->with('parametro')
            ->get();

        $jornadas = JornadaFormacion::query()->get();

        $ambientes = Ambiente::query()
            ->with('piso')
            ->where('status', 1)
            ->orderBy('piso_id')
            ->orderBy('title')
            ->get();

        // Obtener competencias activas
        $competencias = \App\Models\Competencia::query()
            ->activos()
            ->ordenadoPorCodigo()
            ->get(['id', 'codigo', 'nombre']);

        // Obtener guías de aprendizaje activas
        $guias = \App\Models\GuiasAprendizaje::query()
            ->activas()
            ->porNombreAsc()
            ->get(['id', 'codigo', 'nombre']);

        return compact('modalidades', 'jornadas', 'ambientes', 'competencias', 'guias');
    }

    /**
     * Obtener tipos de documento dinámicamente desde el tema-parametro
     */
    public function getTiposDocumento()
    {
        $temaTipoDocumento = $this->temaRepository->obtenerTiposDocumento();

        if (!$temaTipoDocumento) {
            return collect();
        }

        return $temaTipoDocumento->parametros()
            ->where('parametros_temas.status', 1)
            ->orderBy('parametros.name')
            ->get(['parametros.id', 'parametros.name']);
    }

    /**
     * Obtener géneros dinámicamente desde el tema-parametro
     */
    public function getGeneros()
    {
        $temaGenero = $this->temaRepository->obtenerGeneros();

        if (!$temaGenero) {
            return collect();
        }

        return $temaGenero->parametros()
            ->where('parametros_temas.status', 1)
            ->orderBy('parametros.name')
            ->get(['parametros.id', 'parametros.name']);
    }

    /**
     * Verificar si un usuario ya está inscrito en un programa
     */
    public function verificarInscripcionExistente($personaId, $programaId)
    {
        return $this->aspiranteRepository->existeInscripcion($personaId, $programaId);
    }

    /**
     * Crear aspirante complementario
     */
    public function crearAspirante($personaId, $programaId, $observaciones = null)
    {
        return $this->aspiranteRepository->create([
            'persona_id' => $personaId,
            'complementario_id' => $programaId,
            'observaciones' => $observaciones,
            'estado' => 1, // Estado "En proceso"
        ]);
    }

    /**
     * Actualizar estado del aspirante
     */
    public function actualizarEstadoAspirante($aspiranteId, $estado)
    {
        $aspirante = $this->aspiranteRepository->findById($aspiranteId);
        $this->aspiranteRepository->update($aspirante, ['estado' => $estado]);
        return $aspirante;
    }

    /**
     * Obtener estadísticas básicas de un programa
     */
    public function obtenerEstadisticasPrograma($programaId)
    {
        $programa = $this->programaRepository->findWithRelations($programaId);

        if (!$programa) {
            throw new ProgramaNoEncontradoException('Programa no encontrado');
        }

        $totalAspirantes = $this->aspiranteRepository->countByPrograma($programaId);
        $aspirantesActivos = $this->aspiranteRepository->countByEstado($programaId, 1);
        $aspirantesAceptados = $this->aspiranteRepository->countByEstado($programaId, 3);

        return [
            'total_aspirantes' => $totalAspirantes,
            'aspirantes_activos' => $aspirantesActivos,
            'aspirantes_aceptados' => $aspirantesAceptados,
            'cupos_disponibles' => max(0, $programa->cupos - $totalAspirantes),
        ];
    }
}
