<?php

namespace App\Services;

use App\Repositories\InstructorFichaCaracterizacionRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\PersonaRepository;
use App\Repositories\ParametroRepository;
use Illuminate\Support\Facades\Log;

class AsistenceQrService
{

    protected $instructorFichaCaracterizacionRepository;
    protected $instructorRepository;
    protected $personaRepository;
    protected $parametroRepository;

    public function __construct(
        InstructorFichaCaracterizacionRepository $instructorFichaCaracterizacionRepository,
        InstructorRepository $instructorRepository,
        PersonaRepository $personaRepository,
        ParametroRepository $parametroRepository
    )
    {
        $this->instructorFichaCaracterizacionRepository = $instructorFichaCaracterizacionRepository;
        $this->instructorRepository = $instructorRepository;
        $this->personaRepository = $personaRepository;
        $this->parametroRepository = $parametroRepository;
    }

    public function getInstructorFichaIndex($user)
    {
        // Log para debugging
        Log::info('=== DEBUG ASISTENCEQRSERVICE GETINSTRUCTORFICHAINDEX ===');
        Log::info('User persona_id: ' . ($user->persona_id ?? 'NULL'));

        $roleNames = $user?->getRoleNames() ?? collect();
        $isOnlyInstructor = $user && $user->hasRole('INSTRUCTOR') && $roleNames->count() === 1;
        
        $instructor = $this->instructorRepository->getInstructor($user->persona_id);
        
        Log::info('Instructor encontrado: ' . ($instructor ? 'SI' : 'NO'));
        if ($instructor) {
            Log::info('Instructor ID: ' . $instructor->id);
            Log::info('Instructor persona_id: ' . $instructor->persona_id);
        }

        if (!$instructor) {
            Log::warning('No se encontró instructor para el usuario');
            return null;
        }

        $fichas = $this->instructorFichaCaracterizacionRepository->getInstructorFichaCaracterizacion(
            $instructor->id,
            $isOnlyInstructor
        );
        
        Log::info('Fichas obtenidas del repositorio: ' . ($fichas ? 'TIENE DATOS' : 'NULL'));
        if ($fichas) {
            Log::info('Cantidad de fichas desde repositorio: ' . $fichas->count());
        }
        
        Log::info('=== FIN DEBUG SERVICE ===');
        
        return $fichas;
    }

    public function getDiasFormacion()
    {
        return $this->parametroRepository->getDiasFormacion();
    }

    /**
     * Obtiene datos de caracterización con aprendices y horarios
     *
     * @param int $caracterizacionId
     * @param mixed $user
     * @return array
     */
    public function obtenerDatosCaracterizacion(int $caracterizacionId, $user, ?int $asistenciaId = null): array
    {
        Log::info('=== DEBUG OBTENER DATOS CARACTERIZACION ===');
        Log::info('Caracterizacion ID: ' . $caracterizacionId);
        Log::info('User ID: ' . ($user ? $user->id : 'NULL'));
        Log::info('Asistencia ID (filtro tabla): ' . ($asistenciaId ?? 'NULL'));
        
        $fichaCaracterizacion = \App\Models\FichaCaracterizacion::with([
            'diasFormacion.dia',
            'programaFormacion',
            'instructor.persona',
            'jornadaFormacion.parametro'
        ])->find($caracterizacionId);

        Log::info('FichaCaracterizacion encontrada: ' . ($fichaCaracterizacion ? 'SI' : 'NO'));
        if ($fichaCaracterizacion) {
            Log::info('FichaCaracterizacion ID: ' . $fichaCaracterizacion->id);
            Log::info('FichaCaracterizacion ficha: ' . ($fichaCaracterizacion->ficha ?? 'NULL'));
        }

        if (!$fichaCaracterizacion) {
            Log::error('FichaCaracterizacion NO encontrada, retornando null');
            return [
                'fichaCaracterizacion' => null,
                'aprendices' => collect(),
                'horarioHoy' => null,
            ];
        }

        Log::info('=== FIN DEBUG OBTENER DATOS ===');

        // Obtener horario de hoy
        $diaHoy = now()->dayOfWeek;
        $diaId = ($diaHoy == 0) ? 18 : $diaHoy + 11;

        $horarioHoy = null;
        if ($fichaCaracterizacion->diasFormacion) {
            $horarioHoy = $fichaCaracterizacion->diasFormacion
                ->where('dia_id', $diaId)
                ->first();

            if ($horarioHoy) {
                $horarioHoy->hora_inicio = \Carbon\Carbon::parse($horarioHoy->hora_inicio)->format('h:i A');
                $horarioHoy->hora_fin = \Carbon\Carbon::parse($horarioHoy->hora_fin)->format('h:i A');
            }
        }

        // Obtener instructor ficha ID
        $instructorFichaId = null;
        if ($user && $user->persona && $user->persona->instructor) {
            $instructor = $user->persona->instructor;
            $instructorFicha = \App\Models\InstructorFichaCaracterizacion::where('instructor_id', $instructor->id)
                ->where('ficha_id', $fichaCaracterizacion->id)
                ->first();
            if ($instructorFicha) {
                $instructorFichaId = $instructorFicha->id;
            }
        }

        // Obtener aprendices con asistencias
        Log::info('Obteniendo aprendices de la ficha: ' . $fichaCaracterizacion->id);
        $aprendicesFicha = \App\Models\Aprendiz::where('ficha_caracterizacion_id', $fichaCaracterizacion->id)->get();
        Log::info('Cantidad de aprendices encontrados: ' . $aprendicesFicha->count());
        
        foreach ($aprendicesFicha as $index => $aprendiz) {
            Log::info("Aprendiz {$index}: ID={$aprendiz->id}, documento=" . ($aprendiz->persona->numero_documento ?? 'SIN PERSONA'));
        }
        
        $aprendizPersonaConAsistencia = collect();
        $fechaActual = \Carbon\Carbon::now()->format('Y-m-d');

        foreach ($aprendicesFicha as $aprendiz) {
            if ($aprendiz && $aprendiz->persona) {
                $persona = $aprendiz->persona;

                $asistenciaHoy = null;
                if ($instructorFichaId) {
                    $query = \App\Models\AsistenciaAprendiz::where('aprendiz_ficha_id', $aprendiz->id)
                        ->where('instructor_ficha_id', $instructorFichaId);

                    if ($asistenciaId) {
                        $query->where('asistencia_id', $asistenciaId);
                    } else {
                        $query->whereDate('created_at', $fechaActual);
                    }

                    $asistenciaHoy = $query->first();
                }

                $persona->asistenciaHoy = $asistenciaHoy;
                $persona->aprendiz_id = $aprendiz->id; // Agregar el ID del aprendiz

                if ($persona->asistenciaHoy) {
                    $persona->asistenciaHoy->formatted_hora_ingreso = \Carbon\Carbon::parse($persona->asistenciaHoy->hora_ingreso)->format('h:i A');
                    $persona->asistenciaHoy->formatted_hora_salida = $persona->asistenciaHoy->hora_salida
                        ? \Carbon\Carbon::parse($persona->asistenciaHoy->hora_salida)->format('h:i A')
                        : null;
                }

                $aprendizPersonaConAsistencia->push($persona);
            }
        }

        return [
            'fichaCaracterizacion' => $fichaCaracterizacion,
            'aprendices' => $aprendizPersonaConAsistencia,
            'horarioHoy' => $horarioHoy,
        ];
    }
}
