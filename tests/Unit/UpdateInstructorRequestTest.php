<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateInstructorRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateInstructorRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);
    }

    #[Test]
    public function valida_anos_experiencia_rango(): void
    {
        $rules = (new UpdateInstructorRequest)->rules();

        $validator = Validator::make([
            'anos_experiencia' => 60,
        ], $rules);

        if ($validator->fails()) {
            $this->assertArrayHasKey('anos_experiencia', $validator->errors()->toArray());
        }
    }
}
