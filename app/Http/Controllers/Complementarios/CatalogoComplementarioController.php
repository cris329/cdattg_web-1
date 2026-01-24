<?php

namespace App\Http\Controllers\Complementarios;

use App\Configuration\UploadLimits;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\CatalogoComplementarioImportRequest;
use App\Services\Complementarios\CatalogoComplementarioImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogoComplementarioController extends Controller
{
    public function __construct(
        private readonly CatalogoComplementarioImportService $importService
    ) {
        $this->middleware('auth');
        // Usar el mismo permiso que el menú y el CRUD de programas complementarios
        $this->middleware('can:CREAR PROGRAMA COMPLEMENTARIO');
        $this->middleware('validate.content.length:' . UploadLimits::IMPORT_CONTENT_LENGTH_BYTES)
            ->only('store');
    }

    public function create(): View
    {
        return view('complementarios.programas.admin.catalogo_import', [
            'maxFileSizeMb' => UploadLimits::IMPORT_FILE_SIZE_MB,
        ]);
    }

    public function store(CatalogoComplementarioImportRequest $request): RedirectResponse
    {
        $file = $request->file('archivo_catalogo');

        if ($file === null) {
            return redirect()
                ->back()
                ->with('error', 'No se recibió el archivo del catálogo.');
        }

        $totalActualizados = $this->importService->importarCatalogo($file);

        return redirect()
            ->route('complementarios-ofertados.index')
            ->with(
                'success',
                "Catálogo importado correctamente. Programas creados/actualizados: {$totalActualizados}."
            );
    }
}


