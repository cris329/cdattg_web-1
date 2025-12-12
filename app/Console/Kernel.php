<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Procesar salidas pendientes a la medianoche
        $schedule->command('ingreso-salida:procesar-salidas-pendientes')
            ->dailyAt('00:00')
            ->timezone('America/Bogota')
            ->withoutOverlapping()
            ->runInBackground();

        // Verificar préstamos próximos a vencer y enviar recordatorios
        $schedule->job(\App\Jobs\VerificarPrestamosProximosJob::class)
            ->dailyAt('00:00')
            ->timezone('America/Bogota')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
