<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\ProbarRelacionesAprendiz;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProbarRelacionesAprendizCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function command_existe(): void
    {
        $command = new ProbarRelacionesAprendiz;

        $this->assertEquals(
            'aprendices:probar-relaciones',
            $command->getName()
        );
    }

    #[Test]
    public function requiere_argumento_id(): void
    {
        Artisan::call('aprendices:probar-relaciones', ['id' => 999]);

        $output = Artisan::output();
        $this->assertStringContainsString('Probando relaciones del aprendiz ID: 999', $output);
    }

    #[Test]
    public function muestra_error_si_aprendiz_no_existe(): void
    {
        Artisan::call('aprendices:probar-relaciones', ['id' => 999]);

        $this->assertEquals(1, Artisan::exitCode());
    }

    #[Test]
    public function muestra_informacion_del_aprendiz(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        Artisan::call('aprendices:probar-relaciones', ['id' => $aprendiz->id]);

        $output = Artisan::output();
        $this->assertStringContainsString('Aprendiz encontrado', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function prueba_relacion_tipo_documento(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        Artisan::call('aprendices:probar-relaciones', ['id' => $aprendiz->id]);

        $output = Artisan::output();
        $this->assertStringContainsString('Tipo de Documento', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function prueba_relacion_jornada(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        Artisan::call('aprendices:probar-relaciones', ['id' => $aprendiz->id]);

        $output = Artisan::output();
        $this->assertStringContainsString('Jornada', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
