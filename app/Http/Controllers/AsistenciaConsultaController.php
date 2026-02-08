<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaConsultaController extends Controller
{
    private function authorizeAsistencia(Asistencia $asistencia): void
    {
        $user = Auth::user();
        $roleNames = $user?->getRoleNames() ?? collect();
        $isOnlyInstructor = $user && $user->hasRole('INSTRUCTOR') && $roleNames->count() === 1;

        if (!$isOnlyInstructor) {
            return;
        }

        $instructorId = null;
        if ($user?->persona_id) {
            $instructorId = Instructor::where('persona_id', $user->persona_id)->value('id');
        }

        if (!$instructorId) {
            abort(403);
        }

        $isMine = Asistencia::query()
            ->whereKey($asistencia->id)
            ->whereHas('instructorFicha', function ($q) use ($instructorId) {
                $q->where(function ($sub) use ($instructorId) {
                    $sub->where('instructor_id', $instructorId)
                        ->orWhereHas('instructorFicha', function ($aux) use ($instructorId) {
                            $aux->where('instructor_id', $instructorId);
                        });
                });
            })
            ->exists();

        if (!$isMine) {
            abort(403);
        }
    }

    private function buildAprendicesTabla(Asistencia $asistencia)
    {
        $registrosPorAprendizId = $asistencia->asistenciaAprendices
            ->keyBy('aprendiz_ficha_id');

        $aprendicesFicha = $asistencia->instructorFicha?->aprendicesTodos ?? collect();

        return $aprendicesFicha->map(function ($aprendiz) use ($registrosPorAprendizId) {
            $registro = $registrosPorAprendizId->get($aprendiz->id);

            return [
                'aprendiz' => $aprendiz,
                'registro' => $registro,
                'asistio' => (bool) $registro && !is_null($registro->hora_ingreso),
            ];
        });
    }

    public function show(Asistencia $asistencia)
    {
        $this->authorizeAsistencia($asistencia);

        $asistencia->load([
            'evidencia',
            'instructorFicha.programaFormacion',
            'instructorFicha.sede',
            'instructorFicha.ambiente',
            'instructorFicha.instructor.persona',
            'asistenciaAprendices.aprendiz.persona',
            'instructorFicha.aprendicesTodos.persona',
        ]);

        $asistencia->loadCount('asistenciaAprendices');

        $aprendicesTabla = $this->buildAprendicesTabla($asistencia);

        return view('asistencias.show', [
            'asistencia' => $asistencia,
            'aprendicesTabla' => $aprendicesTabla,
        ]);
    }

    public function pdf(Asistencia $asistencia)
    {
        $this->authorizeAsistencia($asistencia);

        $asistencia->load([
            'evidencia',
            'instructorFicha.programaFormacion',
            'instructorFicha.sede',
            'instructorFicha.ambiente',
            'instructorFicha.instructor.persona',
            'asistenciaAprendices.aprendiz.persona',
            'instructorFicha.aprendicesTodos.persona',
        ]);

        $asistencia->loadCount('asistenciaAprendices');

        $aprendicesTabla = $this->buildAprendicesTabla($asistencia);

        $fichaNumero = $asistencia->instructorFicha?->ficha ?? 'N_A';
        $fecha = $asistencia->fecha?->format('Y-m-d') ?? now()->format('Y-m-d');
        $filename = 'asistencia_' . $fichaNumero . '_' . $fecha . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdf.asistencia_consulta', [
            'asistencia' => $asistencia,
            'aprendicesTabla' => $aprendicesTabla,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
