<?php

namespace Tests\Unit;

use App\Models\GuiasResultados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiasResultadosModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_relacion(): void
    {
        $relacion = GuiasResultados::create([
            'guia_aprendizaje_id' => 1,
            'rap_id' => 1,
            'user_create_id' => 1,
        ]);

        $this->assertDatabaseHas('guia_aprendizaje_rap', [
            'id' => $relacion->id,
        ]);
    }
}

