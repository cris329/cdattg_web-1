<?php

namespace Tests\Feature\Inventario;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario\Notificacion;
use App\Models\Inventario\Orden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class NotificacionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar migraciones y seeders de todos los módulos
        $this->migrateDatabases();
        
        // Asegurar que los seeders se ejecuten después de RefreshDatabase
        if (!\App\Models\Tema::where('name', 'CATEGORIAS')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER NOTIFICACION']);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER NOTIFICACION');
    }

    #[Test]
    public function puede_ver_listado_de_notificaciones()
    {
        $this->actingAs($this->user);

        // Crear notificaciones directamente en la base de datos
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1, 'mensaje' => 'Nueva orden']);
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 2, 'mensaje' => 'Otra orden']);

        $response = $this->get(route('inventario.notificaciones.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventario.notificaciones.index');
        $response->assertViewHas('notificaciones');
    }

    #[Test]
    public function no_puede_ver_notificaciones_sin_permiso()
    {
        $userSinPermiso = User::factory()->create();
        $this->actingAs($userSinPermiso);

        $response = $this->get(route('inventario.notificaciones.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_obtener_notificaciones_no_leidas()
    {
        $this->actingAs($this->user);

        // Crear notificaciones no leídas
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1]);
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 2]);

        $response = $this->getJson(route('inventario.notificaciones.unread'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notificaciones',
            'count',
        ]);
        $response->assertJson([
            'count' => 2,
        ]);
    }

    #[Test]
    public function puede_marcar_notificacion_como_leida()
    {
        $this->actingAs($this->user);

        // Crear notificación
        $notificacionId = $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1]);

        $response = $this->postJson(route('inventario.notificaciones.read', $notificacionId));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Notificación marcada como leída',
        ]);

        // Verificar que la notificación fue marcada como leída
        $notificacion = Notificacion::find($notificacionId);
        $this->assertNotNull($notificacion->leida_en);
    }

    #[Test]
    public function retorna_error_al_marcar_notificacion_inexistente()
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('inventario.notificaciones.read', 'notificacion-inexistente'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Notificación no encontrada',
        ]);
    }

    #[Test]
    public function puede_marcar_todas_las_notificaciones_como_leidas()
    {
        $this->actingAs($this->user);

        // Crear notificaciones
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1]);
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 2]);

        $response = $this->postJson(route('inventario.notificaciones.read-all'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);

        // Verificar que todas fueron marcadas como leídas
        $notificaciones = Notificacion::where('user_id', $this->user->id)->get();
        foreach ($notificaciones as $notificacion) {
            $this->assertNotNull($notificacion->leida_en);
        }
    }

    #[Test]
    public function puede_eliminar_notificacion()
    {
        $this->actingAs($this->user);

        // Crear notificación
        $notificacionId = $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1]);

        $response = $this->delete(route('inventario.notificaciones.destroy', $notificacionId));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verificar que la notificación fue eliminada
        $this->assertDatabaseMissing('notificaciones', [
            'id' => $notificacionId,
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function retorna_error_al_eliminar_notificacion_inexistente()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('inventario.notificaciones.destroy', 'notificacion-inexistente'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function puede_eliminar_todas_las_notificaciones()
    {
        $this->actingAs($this->user);

        // Crear notificaciones
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 1]);
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 2]);

        $countAntes = Notificacion::where('user_id', $this->user->id)->count();

        $response = $this->deleteJson(route('inventario.notificaciones.destroy-all'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Todas las notificaciones han sido eliminadas',
            'deleted' => $countAntes,
        ]);

        // Verificar que todas fueron eliminadas
        $this->assertEquals(0, Notificacion::where('user_id', $this->user->id)->count());
    }

    #[Test]
    public function solo_puede_ver_sus_propias_notificaciones()
    {
        $this->actingAs($this->user);

        // Crear otro usuario con notificaciones
        $otroUsuario = User::factory()->create();
        $this->crearNotificacion($otroUsuario->id, 'nueva_orden', ['orden_id' => 1]);

        // Crear notificación para el usuario actual
        $this->crearNotificacion($this->user->id, 'nueva_orden', ['orden_id' => 2]);

        $response = $this->get(route('inventario.notificaciones.index'));

        $response->assertStatus(200);
        
        // Verificar que solo ve sus propias notificaciones
        $notificaciones = $response->viewData('notificaciones');
        foreach ($notificaciones as $notificacion) {
            $this->assertEquals($this->user->id, $notificacion->user_id);
        }
    }

    /**
     * Helper para crear notificaciones directamente en la base de datos
     */
    private function crearNotificacion(int $userId, string $tipo, array $datos): string
    {
        $id = \Illuminate\Support\Str::uuid()->toString();
        
        DB::table('notificaciones')->insert([
            'id' => $id,
            'user_id' => $userId,
            'tipo' => $tipo,
            'datos' => json_encode($datos),
            'leida_en' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}

