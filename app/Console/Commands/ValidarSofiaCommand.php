<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sofia\SofiaValidationService;
use App\Services\Sofia\SofiaValidationProcessor;

class ValidarSofiaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sofia:validar {complementario_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validar estado de registro en SenaSofiaPlus para aspirantes de un programa complementario';

    /**
     * Execute the console command.
     */
    public function handle(
        SofiaValidationService $validationService,
        SofiaValidationProcessor $processor
    ) {
        $complementarioId = $this->argument('complementario_id');

        // Obtener aspirantes que necesitan validación
        $aspirantes = $validationService->getAspirantesToValidate($complementarioId);

        if ($aspirantes->isEmpty()) {
            $this->info('No hay aspirantes que necesiten validación.');
            return;
        }

        $this->info("Validando {$aspirantes->count()} aspirantes...");

        $bar = $this->output->createProgressBar($aspirantes->count());
        $bar->start();

        $exitosos = 0;
        $errores = 0;

        foreach ($aspirantes as $aspirante) {
            $result = $validationService->validateAspirante($aspirante, $complementarioId);

            if ($result['success']) {
                $estado = $result['estado'];
                if ($estado === 1) {
                    $exitosos++;
                }
                $this->info("Cédula {$result['cedula']}: {$result['resultado']}");
            } else {
                $errores++;
                $this->error("Error con cédula {$result['cedula']}: {$result['error']}");
            }

            $bar->advance();

            // Delay para evitar rate limiting
            sleep(2);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Validación completada:");
        $this->info("✅ Registrados: {$exitosos}");
        $this->info("❌ Errores: {$errores}");
    }
}
