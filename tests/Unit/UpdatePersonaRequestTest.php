<?php

namespace Tests\Unit;

use App\Http\Requests\UpdatePersonaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePersonaRequestTest extends TestCase
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
    public function valida_email_formato(): void
    {
        $rules = (new UpdatePersonaRequest)->rules();

        $validator = Validator::make([
            'email' => 'email-invalido',
        ], $rules);

        if ($validator->fails()) {
            $this->assertArrayHasKey('email', $validator->errors()->toArray());
        }
    }
}
