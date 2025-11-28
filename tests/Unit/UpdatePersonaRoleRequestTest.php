<?php

namespace Tests\Unit;

use App\Http\Requests\UpdatePersonaRoleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdatePersonaRoleRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $rol = Role::firstOrCreate(['name' => 'APRENDIZ']);

        $datos = [
            'roles' => [$rol->name],
        ];

        $request = new UpdatePersonaRoleRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function acepta_roles_vacio(): void
    {
        $datos = [
            'roles' => null,
        ];

        $request = new UpdatePersonaRoleRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }
}

