<?php

namespace Tests\Unit;

use App\Models\RegistroActividades;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistroActividadesModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_registro_actividad(): void
    {
        $registro = RegistroActividades::factory()->create();

        $this->assertDatabaseHas('registro_actividades', [
            'id' => $registro->id,
        ]);
    }
}

