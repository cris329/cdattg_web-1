<?php

namespace Tests\Unit;

use App\Http\Requests\PersonaImportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaImportRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valida_archivo_excel_valido(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $datos = [
            'archivo_excel' => $file,
        ];

        $request = new PersonaImportRequest;
        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_archivo_no_excel(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.pdf', 100);

        $datos = [
            'archivo_excel' => $file,
        ];

        $request = new PersonaImportRequest;
        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function rechaza_archivo_faltante(): void
    {
        $datos = [];

        $request = new PersonaImportRequest;
        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->fails());
    }
}

