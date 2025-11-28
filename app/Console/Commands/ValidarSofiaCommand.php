<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sofia\SofiaValidationService;
use App\Services\Sofia\SofiaValidationProcessor;

class ValidarSofiaCommand extends Command
{
    private const DELAY_SECONDS = 2;
    private const ESTADO_REGISTRADO = 1;

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
    ): int {
        $complementarioId = (int) $this->argument('complementario_id');

        $aspirantes = $validationService->getAspirantesToValidate($complementarioId);

        if ($aspirantes->isEmpty()) {
            $this->info('No hay aspirantes que necesiten validacion.');
            return self::SUCCESS;
        }

        $totalAspirantes = $aspirantes->count();
        $this->info("Validando {$totalAspirantes} aspirantes...");

        $bar = $this->output->createProgressBar($totalAspirantes);
        $bar->start();

        $exitosos = 0;
        $errores = 0;

        foreach ($aspirantes as $aspirante) {
            $result = $validationService->validateAspirante($aspirante, $complementarioId);

            if ($result['success']) {
                if ($result['estado'] === self::ESTADO_REGISTRADO) {
                    $exitosos++;
                }
                $this->info("Cedula {$result['cedula']}: {$result['resultado']}");
            } else {
                $errores++;
                $this->error("Error con cedula {$result['cedula']}: {$result['error']}");
            }

            $bar->advance();
            sleep(self::DELAY_SECONDS);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Validacion completada:");
        $this->info("Registrados: {$exitosos}");
        $this->info("Errores: {$errores}");

        return self::SUCCESS;
    }
}
