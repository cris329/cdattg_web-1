<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaisControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_ver_listado_de_paises(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('paises.index'));

        $response->assertStatus(200);
    }
}
