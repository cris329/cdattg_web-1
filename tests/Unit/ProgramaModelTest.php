<?php

namespace Tests\Unit;

use App\Models\Programa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgramaModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_programa(): void
    {
        $programa = Programa::factory()->create();

        $this->assertDatabaseHas('programas', [
            'id' => $programa->id,
        ]);
    }
}

