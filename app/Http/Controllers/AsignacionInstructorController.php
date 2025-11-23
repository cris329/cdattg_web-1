<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsignacionInstructorRequest;
use App\Http\Requests\UpdateAsignacionInstructorRequest;
use App\Models\AsignacionInstructor;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionInstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $asignaciones = AsignacionInstructor::with([
            'ficha.programaFormacion',
            'instructor.persona',
            'competencia',
            'resultadosAprendizaje',
        ])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('asignaciones.index', compact('asignaciones'));
    }

    public function create(): View
    {
        $fichas = FichaCaracterizacion::with('programaFormacion')
            ->orderBy('ficha')
            ->get();

        $instructores = Instructor::with('persona')
            ->orderBy('nombre_completo_cache')
            ->get();

        return view('asignaciones.create', compact('fichas', 'instructores'));
    }

    public function store(StoreAsignacionInstructorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $existe = AsignacionInstructor::where('ficha_id', $validated['ficha_id'])
            ->where('instructor_id', $validated['instructor_id'])
            ->where('competencia_id', $validated['competencia_id'])
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'La asignación ya existe para esta ficha, instructor y competencia.');
        }

        try {
            DB::transaction(function () use ($validated) {
                $asignacion = AsignacionInstructor::create([
                    'ficha_id' => $validated['ficha_id'],
                    'instructor_id' => $validated['instructor_id'],
                    'competencia_id' => $validated['competencia_id'],
                ]);

                $asignacion->resultadosAprendizaje()->sync($validated['resultados']);
            });
        } catch (\Throwable $e) {
            Log::error('Error al guardar asignación de instructor', [
                'payload' => $validated,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al guardar la asignación. Intente nuevamente.');
        }

        return redirect()
            ->route('asignaciones.instructores.index')
            ->with('success', 'Asignación registrada correctamente.');
    }

    public function show(AsignacionInstructor $asignacion): View
    {
        $asignacion->load([
            'ficha.programaFormacion',
            'instructor.persona',
            'competencia',
            'resultadosAprendizaje',
        ]);

        return view('asignaciones.show', compact('asignacion'));
    }

    public function edit(AsignacionInstructor $asignacion): View
    {
        $asignacion->load([
            'ficha.programaFormacion',
            'instructor.persona',
            'competencia',
            'resultadosAprendizaje',
        ]);

        $fichas = FichaCaracterizacion::with('programaFormacion')
            ->orderBy('ficha')
            ->get();

        $instructores = Instructor::with('persona')
            ->orderBy('nombre_completo_cache')
            ->get();

        return view('asignaciones.edit', compact('asignacion', 'fichas', 'instructores'));
    }

    public function update(UpdateAsignacionInstructorRequest $request, AsignacionInstructor $asignacion): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $asignacion) {
                $asignacion->update([
                    'ficha_id' => $validated['ficha_id'],
                    'instructor_id' => $validated['instructor_id'],
                    'competencia_id' => $validated['competencia_id'],
                ]);

                $asignacion->resultadosAprendizaje()->sync($validated['resultados']);
            });
        } catch (\Throwable $e) {
            Log::error('Error al actualizar asignación de instructor', [
                'asignacion_id' => $asignacion->id,
                'payload' => $validated,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar la asignación. Intente nuevamente.');
        }

        return redirect()
            ->route('asignaciones.instructores.show', $asignacion)
            ->with('success', 'Asignación actualizada correctamente.');
    }

    public function competenciasPorFicha(FichaCaracterizacion $ficha, Request $request): JsonResponse
    {
        if (!$ficha->programaFormacion) {
            return response()->json([
                'data' => [],
            ]);
        }

        // Si estamos editando, obtener el instructor_ficha_id
        $instructorFichaId = $request->input('instructor_ficha_id');
        $competenciaAsignadaId = null;

        // Si estamos editando, obtener la competencia asignada al instructor
        if ($instructorFichaId) {
            $instructorFicha = \App\Models\InstructorFichaCaracterizacion::find($instructorFichaId);
            if ($instructorFicha && $instructorFicha->competencia_id) {
                $competenciaAsignadaId = $instructorFicha->competencia_id;
            }
        }

        // Obtener todos los resultados de aprendizaje ya asignados en esta ficha
        // Excluir los resultados asignados al instructor actual si estamos editando
        $queryResultadosAsignados = \DB::table('instructor_ficha_resultados_aprendizaje')
            ->join('instructor_fichas_caracterizacion', 'instructor_ficha_resultados_aprendizaje.instructor_ficha_id', '=', 'instructor_fichas_caracterizacion.id')
            ->where('instructor_fichas_caracterizacion.ficha_id', $ficha->id);
        
        if ($instructorFichaId) {
            // Excluir resultados asignados al instructor actual
            $queryResultadosAsignados->where('instructor_fichas_caracterizacion.id', '!=', $instructorFichaId);
        }
        
        $resultadosAsignados = $queryResultadosAsignados
            ->pluck('instructor_ficha_resultados_aprendizaje.resultado_aprendizaje_id')
            ->toArray();

        // Obtener competencias del programa que tengan resultados de aprendizaje
        $competencias = $ficha->programaFormacion->competencias()
            ->with(['resultadosAprendizaje' => function($query) {
                $query->select('resultados_aprendizajes.id', 'resultados_aprendizajes.codigo', 'resultados_aprendizajes.nombre');
            }])
            ->select('competencias.id', 'competencias.codigo', 'competencias.nombre')
            ->orderBy('competencias.nombre')
            ->get();

        // Filtrar competencias que tengan al menos un resultado sin asignar
        // O incluir la competencia asignada al instructor actual si estamos editando
        $competenciasConResultadosSinAsignar = $competencias->filter(function($competencia) use ($resultadosAsignados, $competenciaAsignadaId) {
            // Si es la competencia asignada al instructor actual, siempre incluirla
            if ($competenciaAsignadaId && $competencia->id == $competenciaAsignadaId) {
                return true;
            }
            
            // Obtener IDs de resultados de esta competencia
            $resultadosCompetencia = $competencia->resultadosAprendizaje->pluck('id')->toArray();
            
            // Verificar si hay algún resultado que no esté asignado
            $resultadosSinAsignar = array_diff($resultadosCompetencia, $resultadosAsignados);
            
            // Solo incluir si hay al menos un resultado sin asignar
            return !empty($resultadosSinAsignar);
        })->map(function($competencia) {
            // Retornar solo los datos necesarios
            return [
                'id' => $competencia->id,
                'codigo' => $competencia->codigo,
                'nombre' => $competencia->nombre,
            ];
        })->values();

        return response()->json([
            'data' => $competenciasConResultadosSinAsignar,
        ]);
    }

    public function resultadosPorCompetencia(Competencia $competencia, Request $request): JsonResponse
    {
        // Obtener el ficha_id y instructor_ficha_id de la solicitud (query parameters)
        $fichaId = $request->input('ficha_id');
        $instructorFichaId = $request->input('instructor_ficha_id');
        
        // Obtener todos los resultados de aprendizaje de la competencia
        $resultados = $competencia->resultadosAprendizaje()
            ->select('resultados_aprendizajes.id', 'resultados_aprendizajes.codigo', 'resultados_aprendizajes.nombre', 'resultados_aprendizajes.duracion')
            ->orderBy('resultados_aprendizajes.codigo')
            ->get();

        // Si se proporciona ficha_id, filtrar resultados ya asignados
        if ($fichaId) {
            // Obtener resultados ya asignados en esta ficha
            // Excluir los resultados asignados al instructor actual si estamos editando
            $queryResultadosAsignados = \DB::table('instructor_ficha_resultados_aprendizaje')
                ->join('instructor_fichas_caracterizacion', 'instructor_ficha_resultados_aprendizaje.instructor_ficha_id', '=', 'instructor_fichas_caracterizacion.id')
                ->where('instructor_fichas_caracterizacion.ficha_id', $fichaId);
            
            if ($instructorFichaId) {
                // Excluir resultados asignados al instructor actual
                $queryResultadosAsignados->where('instructor_fichas_caracterizacion.id', '!=', $instructorFichaId);
            }
            
            $resultadosAsignados = $queryResultadosAsignados
                ->pluck('instructor_ficha_resultados_aprendizaje.resultado_aprendizaje_id')
                ->toArray();

            // Filtrar solo los resultados que no estén asignados
            // O incluir los resultados asignados al instructor actual si estamos editando
            $resultados = $resultados->reject(function($resultado) use ($resultadosAsignados, $instructorFichaId) {
                // Si estamos editando, verificar si este resultado está asignado al instructor actual
                if ($instructorFichaId) {
                    $resultadoAsignadoAlInstructor = \DB::table('instructor_ficha_resultados_aprendizaje')
                        ->where('instructor_ficha_id', $instructorFichaId)
                        ->where('resultado_aprendizaje_id', $resultado->id)
                        ->exists();
                    
                    // Si está asignado al instructor actual, incluirlo
                    if ($resultadoAsignadoAlInstructor) {
                        return false; // No rechazar (incluir)
                    }
                }
                
                // Rechazar si está asignado a otro instructor
                return in_array($resultado->id, $resultadosAsignados);
            })->values();
        }

        return response()->json([
            'data' => $resultados,
        ]);
    }
}

