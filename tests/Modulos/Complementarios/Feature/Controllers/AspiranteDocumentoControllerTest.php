<?php

declare(strict_types=1);

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;
use Tests\Complementarios\Concerns\AspiranteTestHelpers;

/**
 * Tests para funcionalidad de validación de documentos de aspirantes.
 * RF-ASP-011: Validar Documentos en Google Drive
 */
class AspiranteDocumentoControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;
    use AspiranteTestHelpers;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        
        $this->seedComplementariosDatabaseIfNeeded();
        
        $this->user = User::factory()->create();
    }

    // ==========================================
    // RF-ASP-011: Validar Documentos en Google Drive
    // ==========================================

    #[Test]
    public function puede_validar_documentos_de_aspirantes()
    {
        $this->actingAs($this->user);
        
        $programa = $this->crearProgramaComplementario();
        AspiranteComplementario::factory()->count(2)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    #[Test]
    public function retorna_error_si_no_hay_aspirantes_para_validar_documentos()
    {
        $this->actingAs($this->user);
        
        $programa = $this->crearProgramaComplementario();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function validar_documentos_retorna_resultado_correcto()
    {
        $this->actingAs($this->user);
        
        $programa = $this->crearProgramaComplementario();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }
}

