<?php

namespace App\Repositories;

use App\Models\InstructorFichaCaracterizacion;
use Illuminate\Support\Facades\Log;

class InstructorFichaCaracterizacionRepository
{
    public function getInstructorFichaCaracterizacion($instructorId, bool $onlyActive = false)
    {
        Log::info('=== DEBUG INSTRUCTORFICHACARACTERIZACIONREPOSITORY ===');
        Log::info('Buscando fichas para instructor_id: ' . $instructorId);
        
        $fichas = InstructorFichaCaracterizacion::query()
            ->with([
                'ficha.programaFormacion',
                'ficha.sede',
                'ficha.ambiente',
                'ficha.instructor.persona',
                'ficha.modalidadFormacion',
                'ficha.jornadaFormacion.parametro',
                'ficha.aprendices',
            ])
            ->where('instructor_id', $instructorId)
            ->when($onlyActive, function ($query) {
                $query->whereHas('ficha', function ($q) {
                    $q->where('status', 1);
                });
            })
            ->get();
        
        Log::info('Cantidad de fichas encontradas: ' . $fichas->count());
        
        if ($fichas->isNotEmpty()) {
            Log::info('Fichas encontradas:');
            foreach ($fichas as $index => $ficha) {
                Log::info("  Ficha {$index}: ID={$ficha->id}, instructor_id={$ficha->instructor_id}, ficha_id={$ficha->ficha_id}");
            }
        } else {
            Log::warning('No se encontraron fichas para el instructor_id: ' . $instructorId);
        }
        
        Log::info('=== FIN DEBUG REPOSITORIO FICHAS ===');
        
        return $fichas;
    }
}
