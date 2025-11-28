<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(AuthService::class);
    }

    #[Test]
    public function puede_intentar_login_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $resultado = $this->service->intentarLogin('test@example.com', 'password123');

        $this->assertTrue($resultado['success']);
    }

    #[Test]
    public function no_puede_intentar_login_con_credenciales_invalidas(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $resultado = $this->service->intentarLogin('test@example.com', 'wrongpassword');

        $this->assertFalse($resultado['success']);
    }
}
