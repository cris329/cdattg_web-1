<?php

namespace App\Console\Commands;

use App\Models\FichaCaracterizacion;
use App\Models\InstructorFichaCaracterizacion;
use Illuminate\Console\Command;

class SincronizarInstructorLiderEnPivot extends Command
{
    protected $signature = 'fichas:sincronizar-instructor-lider';

    protected $description = 'Crea registros en instructor_fichas_caracterizacion para fichas que tienen instructor_id pero no tienen fila en la tabla pivote (para que "Tomar asistencia" muestre las fichas).';

    public function handle(): int
    {
        $fichas = FichaCaracterizacion::whereNotNull('instructor_id')->get();
        $creados = 0;

        foreach ($fichas as $ficha) {
            $yaExiste = InstructorFichaCaracterizacion::where('instructor_id', $ficha->instructor_id)
                ->where('ficha_id', $ficha->id)
                ->exists();

            if (!$yaExiste) {
                InstructorFichaCaracterizacion::create([
                    'instructor_id' => $ficha->instructor_id,
                    'ficha_id' => $ficha->id,
                    'fecha_inicio' => $ficha->fecha_inicio,
                    'fecha_fin' => $ficha->fecha_fin,
                    'total_horas_instructor' => $ficha->total_horas ?? 0,
                ]);
                $creados++;
                $this->info("Creado pivot para ficha {$ficha->ficha} (id {$ficha->id}), instructor_id {$ficha->instructor_id}");
            }
        }

        $this->info("Listo. Registros creados: {$creados}");
        return self::SUCCESS;
    }
}
