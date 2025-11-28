<?php

namespace Tests\Unit;

use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_login(): void
    {
        $login = Login::factory()->create();

        $this->assertDatabaseHas('logins', [
            'id' => $login->id,
        ]);
    }
}

