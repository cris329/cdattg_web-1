<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\RefactorSonarQubeCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RefactorSonarQubeCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_existe(): void
    {
        $command = new RefactorSonarQubeCommand;

        $this->assertEquals(
            'refactor:sonarqube',
            $command->getName()
        );
    }

    #[Test]
    public function solo_se_ejecuta_en_entorno_desarrollo(): void
    {
        config(['app.env' => 'production']);

        Artisan::call('refactor:sonarqube', ['--path' => 'app']);

        $output = Artisan::output();
        $this->assertStringContainsString('solo puede ejecutarse en entorno de desarrollo', $output);
        $this->assertEquals(1, Artisan::exitCode());
    }

    #[Test]
    public function ejecuta_en_modo_dry_run(): void
    {
        config(['app.env' => 'testing']);

        Artisan::call('refactor:sonarqube', [
            '--path' => 'app',
            '--dry-run' => true,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('DRY-RUN', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_error_si_ruta_no_existe(): void
    {
        config(['app.env' => 'testing']);

        Artisan::call('refactor:sonarqube', [
            '--path' => 'ruta/inexistente',
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('no existe', $output);
        $this->assertEquals(1, Artisan::exitCode());
    }
}
