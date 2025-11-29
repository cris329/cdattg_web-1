<?php

namespace Tests\Unit\Inventario;

use App\Models\Inventario\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificacionModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        // Crear un User directamente sin PersonaSeeder para evitar dependencias
        $user = User::first();
        if (!$user) {
            // Crear una Persona básica primero
            $persona = \App\Models\Persona::factory()->create();
            $user = User::factory()->create(['persona_id' => $persona->id]);
        }
        $this->user = $user;
    }

    #[Test]
    public function obtiene_data_attribute(): void
    {
        $notificacion = Notificacion::create([
            'id' => 'test-id',
            'tipo' => 'test',
            'notificable_type' => User::class,
            'notificable_id' => $this->user->id,
            'datos' => ['campo' => 'valor'],
        ]);

        $this->assertEquals(['campo' => 'valor'], $notificacion->data);
    }

    #[Test]
    public function obtiene_type_attribute(): void
    {
        $notificacion = Notificacion::create([
            'id' => 'test-id-2',
            'tipo' => 'test-type',
            'notificable_type' => User::class,
            'notificable_id' => $this->user->id,
            'datos' => [],
        ]);

        $this->assertEquals('test-type', $notificacion->type);
    }

    #[Test]
    public function marca_como_leida(): void
    {
        $notificacion = Notificacion::create([
            'id' => 'test-id-3',
            'tipo' => 'test',
            'notificable_type' => User::class,
            'notificable_id' => $this->user->id,
            'datos' => [],
            'leida_en' => null,
        ]);

        $notificacion->markAsRead();

        $this->assertNotNull($notificacion->fresh()->leida_en);
    }

    #[Test]
    public function verifica_si_esta_leida(): void
    {
        $notificacion = Notificacion::create([
            'id' => 'test-id-4',
            'tipo' => 'test',
            'notificable_type' => User::class,
            'notificable_id' => $this->user->id,
            'datos' => [],
            'leida_en' => now(),
        ]);

        $this->assertTrue($notificacion->read());
        $this->assertFalse($notificacion->unread());
    }
}

