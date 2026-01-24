<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\StoreProgramaComplementarioRequest;
use App\Http\Requests\Complementarios\UpdateProgramaComplementarioRequest;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Services\Complementarios\ComplementarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramaComplementarioController extends Controller
{
    public function __construct(
        private readonly ComplementarioService $complementarioService
    ) {}

    /**
     * Listar programas complementarios ofertados (Admin).
     */
    public function index(): View
    {
        $programas = $this->complementarioService
            ->obtenerProgramas(['modalidad.parametro', 'jornada', 'diasFormacion', 'ambiente']);

        $programas = $this->complementarioService->enriquecerProgramas($programas);

        return view(
            'complementarios.programas.admin.index',
            array_merge(
                ['programas' => $programas],
                $this->complementarioService->obtenerDatosFormulario()
            )
        );
    }

    /**
     * Mostrar formulario de creación de programa
     */
    public function create(): View
    {
        return view(
            'complementarios.programas.admin.create',
            $this->complementarioService->obtenerDatosFormulario()
        );
    }

    /**
     * Mostrar programas públicos (Vista pública)
     */
    public function programasPublicos(): View
    {
        $programas = $this->complementarioService
            ->obtenerProgramas(['modalidad.parametro', 'jornada', 'diasFormacion'], estado: 1);

        $programas = $this->complementarioService->enriquecerProgramas($programas);

        return view('complementarios.programas.public.index', [
            'programas' => $programas,
            'tiposDocumento' => $this->complementarioService->getTiposDocumento(),
            'generos' => $this->complementarioService->getGeneros(),
        ]);
    }

    /**
     * Mostrar todos los programas (Admin)
     */
    public function verProgramas(): View
    {
        $programas = $this->complementarioService
            ->obtenerProgramas(['modalidad.parametro', 'jornada', 'diasFormacion']);

        $programas = $this->complementarioService->enriquecerProgramas($programas);

        return view('complementarios.ver_programas', ['programas' => $programas]);
    }

    /**
     * Mostrar programa específico público
     */
    public function verPrograma(ComplementarioOfertado $programa): View
    {
        $programa->load(['modalidad.parametro', 'jornada', 'diasFormacion']);
        $programa = $this->complementarioService->enriquecerPrograma($programa);

        $programaData = [
            'id' => $programa->id,
            'nombre' => $programa->nombre,
            'justificacion' => $programa->justificacion,
            'requisitos_ingreso' => $programa->requisitos_ingreso,
            'duracion' => ($programa->duracion ?? 0) . ' horas',
            'icono' => $programa->icono,
            'modalidad' => $programa->modalidad_nombre ?? 'N/A',
            'jornada' => $programa->jornada_nombre ?? 'N/A',
            'dias' => $this->formatearDiasFormacion($programa),
            'dias_detalle' => $this->mapearDiasFormacionPublico($programa),
            'cupos' => $programa->cupos,
            'estado' => $programa->estado_label,
        ];

        return view('complementarios.programas.public.show', compact('programaData'));
    }

    /**
     * Mostrar detalles del programa (Vista)
     */
    public function show(ComplementarioOfertado $programa): View
    {
        $programa->load(['modalidad.parametro', 'jornada', 'diasFormacion', 'ambiente.piso', 'competencias', 'raps', 'estado.parametro']);
        $programa = $this->complementarioService->enriquecerPrograma($programa);

        return view(
            'complementarios.programas.admin.show',
            array_merge(
                ['programa' => $programa],
                $this->complementarioService->obtenerDatosFormulario()
            )
        );
    }

    /**
     * Mostrar formulario de edición (Vista)
     */
    public function edit(ComplementarioOfertado $programa): View
    {
        $programa->load(['modalidad', 'jornada', 'diasFormacion', 'ambiente', 'competencias', 'raps', 'guiasAprendizaje']);

        $dias = $this->mapearDiasFormacion($programa);

        $datosFormulario = $this->complementarioService->obtenerDatosFormulario();

        return view(
            'complementarios.programas.admin.edit',
            array_merge(
                [
                    'programa' => $programa,
                    'diasSeleccionados' => $dias,
                    'competenciasSeleccionadas' => $programa->competencias->pluck('id')->toArray(),
                    'rapsSeleccionados' => $programa->raps->pluck('id')->toArray(),
                    'guiasSeleccionadas' => $programa->guiasAprendizaje->pluck('id')->toArray(),
                ],
                $datosFormulario
            )
        );
    }

    /**
     * API: Obtener datos de programa para edición (AJAX)
     */
    public function editApi(ComplementarioOfertado $programa): JsonResponse
    {
        $programa->load(['modalidad', 'jornada', 'diasFormacion', 'ambiente']);

        $dias = $this->mapearDiasFormacion($programa);

        return response()->json([
            'id' => $programa->id,
            'codigo' => $programa->codigo,
            'nombre' => $programa->nombre,
            'justificacion' => $programa->justificacion,
            'requisitos_ingreso' => $programa->requisitos_ingreso,
            'duracion' => $programa->duracion,
            'cupos' => $programa->cupos,
            'estado' => $programa->estado,
            'modalidad_id' => $programa->modalidad_id,
            'jornada_id' => $programa->jornada_id,
            'ambiente_id' => $programa->ambiente_id,
            'ambiente_comentario' => $programa->ambiente_comentario,
            'dias' => $dias,
        ]);
    }

    /**
     * Crear nuevo programa
     */
    public function store(StoreProgramaComplementarioRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        DB::transaction(function () use ($payload) {
            $atributos = $this->extractProgramaAtributos($payload);
            // NOTA: Las columnas user_create_id y user_edit_id no existen en la tabla
            // Se han removido para evitar el error SQL

            $programa = ComplementarioOfertado::create($atributos);

            // Sincronizar días de formación
            $this->complementarioService->sincronizarDiasFormacion($programa, $payload['dias'] ?? null);

            // Sincronizar estructura académica
            $this->sincronizarEstructuraAcademica($programa, $payload);
        });

        return redirect()
            ->route('complementarios-ofertados.index')
            ->with('success', 'Programa creado exitosamente.');
    }

    /**
     * Actualizar programa
     */
    public function update(
        UpdateProgramaComplementarioRequest $request,
        ComplementarioOfertado $programa
    ): RedirectResponse {
        $payload = $request->validated();

        DB::transaction(function () use ($programa, $payload) {
            $atributos = $this->extractProgramaAtributos($payload);
            // NOTA: La columna user_edit_id no existe en la tabla
            // Se ha removido para evitar el error SQL

            $programa->update($atributos);

            $this->complementarioService->sincronizarDiasFormacion($programa, $payload['dias'] ?? null);

            // Sincronizar estructura académica
            $this->sincronizarEstructuraAcademica($programa, $payload);
        });

        return redirect()
            ->route('complementarios-ofertados.show', $programa->id)
            ->with('success', 'Programa actualizado exitosamente.');
    }

    /**
     * Eliminar programa
     */
    public function destroy(ComplementarioOfertado $programa): JsonResponse
    {
        // Verificar si hay registros relacionados antes de eliminar
        $relacionesActivas = $this->obtenerRelacionesActivas($programa);
        
        if (!empty($relacionesActivas)) {
            $mensaje = $this->construirMensajeErrorRelaciones($relacionesActivas);

            return response()->json([
                'success' => false,
                'message' => $mensaje,
            ], 422);
        }

        try {
            $programa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programa eliminado exitosamente.',
            ]);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    /**
     * Extrae los atributos permitidos para crear o actualizar un programa.
     *
     * @return array<string, mixed>
     */
    private function extractProgramaAtributos(array $payload): array
    {
        $atributos = collect($payload)->only([
            'catalogo_id',
            'codigo',
            'justificacion',
            'cupos',
            'jornada_id',
            'ambiente_id',
            'ambiente_comentario',
        ])->toArray();

        // Si se seleccionó un programa del catálogo, sobrescribir datos básicos
        if (!empty($payload['catalogo_id'])) {
            /** @var \App\Models\Complementarios\ComplementarioCatalogo|null $catalogo */
            $catalogo = \App\Models\Complementarios\ComplementarioCatalogo::query()
                ->find($payload['catalogo_id']);

            if ($catalogo !== null) {
                $atributos['catalogo_id'] = $catalogo->id;
                $atributos['codigo'] = $catalogo->prf_codigo;
            }
        }

        // Convertir estado legacy (0,1,2) a estado_id (ID de ParametroTema)
        if (isset($payload['estado'])) {
            $estadoId = $this->complementarioService->convertirEstadoLegacyAEstadoId((int) $payload['estado']);
            if ($estadoId) {
                $atributos['estado_id'] = $estadoId;
            }
        }

        return $atributos;
    }

    /**
     * Sincroniza la estructura académica del programa complementario.
     */
    private function sincronizarEstructuraAcademica(ComplementarioOfertado $programa, array $payload): void
    {
        if (isset($payload['competencias'])) {
            $programa->competencias()->sync($payload['competencias']);
        }

        if (isset($payload['raps'])) {
            $programa->raps()->sync($payload['raps']);
        }

        if (isset($payload['guias'])) {
            $programa->guiasAprendizaje()->sync($payload['guias']);
        }
    }

    /**
     * Obtiene las relaciones activas de un programa complementario.
     */
    private function obtenerRelacionesActivas(ComplementarioOfertado $programa): array
    {
        $relaciones = [];

        if ($programa->aspirantes()->exists()) {
            $relaciones[] = 'aspirantes inscritos';
        }
        
        if ($programa->competencias()->exists()) {
            $relaciones[] = 'competencias asociadas';
        }
        
        if ($programa->raps()->exists()) {
            $relaciones[] = 'resultados de aprendizaje (RAPs) asociados';
        }
        
        if ($programa->guiasAprendizaje()->exists()) {
            $relaciones[] = 'guías de aprendizaje asociadas';
        }
        
        if ($programa->diasFormacion()->exists()) {
            $relaciones[] = 'días de formación asignados';
        }

        return $relaciones;
    }

    /**
     * Construye el mensaje de error para relaciones activas.
     */
    private function construirMensajeErrorRelaciones(array $relaciones): string
    {
        $mensaje = 'No se puede eliminar el programa porque tiene ' . implode(', ', $relaciones) . '. ';
        $mensaje .= 'Por favor, elimine estas relaciones primero o cambie el estado del programa a "Sin Oferta".';
        
        return $mensaje;
    }

    /**
     * Maneja excepciones durante la eliminación.
     */
    private function manejarExcepcion(\Exception $e): JsonResponse
    {
        // Capturar excepción de integridad referencial (código 23000 para violación de restricción de clave foránea)
        if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el programa porque tiene registros relacionados en el sistema. Por favor, elimine primero todas las relaciones (aspirantes, competencias, RAPs, guías de aprendizaje, días de formación) o cambie el estado del programa a "Sin Oferta".',
            ], 422);
        }
        
        // Para otras excepciones, usar mensaje genérico
        Log::error('Error al eliminar programa complementario', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
            'exception_type' => get_class($e)
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error al intentar eliminar el programa. Por favor, intente nuevamente.',
        ], 500);
    }

    /**
     * Mapea los días de formación de un programa a un formato estructurado.
     *
     * @param ComplementarioOfertado $programa
     * @return array<int, array<string, mixed>>
     */
    private function mapearDiasFormacion(ComplementarioOfertado $programa): array
    {
        return $programa->diasFormacion->map(static function ($dia) {
            // El dia_id en el pivot es el ID del parámetro/día
            // Usamos el ID del modelo relacionado (Parametro)
            return [
                'dia_id' => (int) $dia->id,
                'hora_inicio' => $dia->pivot->hora_inicio ? substr($dia->pivot->hora_inicio, 0, 5) : null,
                'hora_fin' => $dia->pivot->hora_fin ? substr($dia->pivot->hora_fin, 0, 5) : null,
            ];
        })->toArray();
    }

    /**
     * Formatea los días de formación para visualización.
     */
    private function formatearDiasFormacion(ComplementarioOfertado $programa): string
    {
        return $programa->diasFormacion->map(static function ($dia) {
            $nombreDia = $dia->parametro?->name ?? 'Día';
            return $nombreDia . ' (' . $dia->pivot->hora_inicio . ' - ' . $dia->pivot->hora_fin . ')';
        })->implode(', ');
    }

    /**
     * Mapea los días de formación para la vista pública en formato estructurado.
     *
     * @return array<int, array{dia: string, hora_inicio: string|null, hora_fin: string|null}>
     */
    private function mapearDiasFormacionPublico(ComplementarioOfertado $programa): array
    {
        return $programa->diasFormacion
            ->map(static function ($dia) {
                return [
                    'dia' => (string) ($dia->parametro?->name ?? 'Día'),
                    'hora_inicio' => $dia->pivot->hora_inicio ? substr((string) $dia->pivot->hora_inicio, 0, 5) : null,
                    'hora_fin' => $dia->pivot->hora_fin ? substr((string) $dia->pivot->hora_fin, 0, 5) : null,
                ];
            })
            ->values()
            ->all();
    }
}
