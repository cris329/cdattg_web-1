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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function obtiene_data_attribute(): void
    {
        $notificacion = Notificacion::create([
            'id' => 'test-id',
            'tipo' => 'test',
            'notificable_type' => User::class,
            'notificable_id' => 1,
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
            'notificable_id' => 1,
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
            'notificable_id' => 1,
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
            'notificable_id' => 1,
            'leida_en' => now(),
        ]);

        $this->assertTrue($notificacion->read());
        $this->assertFalse($notificacion->unread());
    }
}

