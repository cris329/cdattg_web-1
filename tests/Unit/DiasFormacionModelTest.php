<?php

namespace Tests\Unit;

use App\Models\DiasFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiasFormacionModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_dia_formacion(): void
    {
        $dia = DiasFormacion::create([
            'nombre' => 'Lunes',
        ]);

        $this->assertDatabaseHas('dias_formacion', [
            'id' => $dia->id,
            'nombre' => 'Lunes',
        ]);
    }
}

