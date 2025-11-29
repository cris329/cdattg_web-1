<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\InscripcionComplementarioRequest;
use App\Http\Requests\Complementarios\InscripcionGeneralRequest;
use App\Services\InscripcionComplementarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InscripcionComplementarioController extends Controller
{
    public function __construct(
        private readonly InscripcionComplementarioService $inscripcionService
    ) {}

    /**
     * Mostrar formulario general de inscripción a eventos del SENA
     */
    public function inscripcionGeneral(): View
    {
        $data = $this->inscripcionService->prepararFormularioGeneral();

        return view('complementarios.inscripciones.general', $data);
    }

    /**
     * Procesar la inscripción general (solo datos de persona y caracterización)
     */
    public function procesarInscripcionGeneral(InscripcionGeneralRequest $request): RedirectResponse
    {
        return $this->inscripcionService->procesarInscripcionGeneral($request->validated());
    }

    /**
     * Mostrar formulario de inscripción a programa específico
     */
    public function formularioInscripcion(int $id): View
    {
        $data = $this->inscripcionService->prepararFormularioInscripcion($id);

        return view('complementarios.inscripciones.create', $data);
    }

    /**
     * Procesar la inscripción del aspirante
     */
    public function procesarInscripcion(InscripcionComplementarioRequest $request, int $id): RedirectResponse
    {
        return $this->inscripcionService->procesarInscripcion($request->validated(), $id);
    }
}
