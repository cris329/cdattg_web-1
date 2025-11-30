<?php

namespace App\Services\Complementarios;

use App\Exceptions\ProcesarDocumentoIdentidadException;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Departamento;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\User;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Repositories\TemaRepository;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InscripcionComplementarioService
{
    public function __construct(
        private readonly PersonaRepository $personaRepository,
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly TemaRepository $temaRepository,
        private readonly \App\Services\Complementarios\ComplementarioService $complementarioService,
        private readonly UserService $userService
    ) {}

    /**
     * Preparar datos para el formulario de inscripción general
     */
    public function prepararFormularioGeneral(): array
    {
        $categoriasConHijos = $this->obtenerCaracterizacionesAgrupadas();
        $paises = Pais::all();
        $departamentos = Departamento::all();
        $tiposDocumento = $this->complementarioService->getTiposDocumento();
        $generos = $this->complementarioService->getGeneros();

        return compact('categoriasConHijos', 'paises', 'departamentos', 'tiposDocumento', 'generos');
    }

    /**
     * Procesar inscripción general (solo datos de persona)
     */
    public function procesarInscripcionGeneral(array $data): RedirectResponse
    {
        try {
            // Verificar si ya existe una persona con el mismo documento o email
            if ($this->personaRepository->existsByDocumentoOrEmail($data['numero_documento'], $data['email'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe una persona registrada con este número de documento o correo electrónico.');
            }

            // Convertir parametro_id a parametros_temas.id
            $data = $this->convertirParametrosAParametrosTemas($data);

            // Crear nueva persona
            $this->personaRepository->create($data);

            return redirect()
                ->route('inscripcion.general')
                ->with('success', '¡Registro exitoso! Sus datos han sido guardados correctamente.');

        } catch (\Exception $e) {
            Log::error('Error en inscripción general: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al procesar su inscripción. Por favor intente nuevamente.');
        }
    }

    /**
     * Preparar datos para el formulario de inscripción a programa específico
     */
    public function prepararFormularioInscripcion(int $programaId): array
    {
        $programa = $this->programaRepository->findWithRelations($programaId, ['modalidad.parametro', 'jornada']);

        if (!$programa) {
            abort(404, 'Programa no encontrado');
        }

        $documentos = $this->buildTemaPayload(
            $this->temaRepository->obtenerTiposDocumento(),
            $this->complementarioService->getTiposDocumento()
        );

        $generos = $this->buildTemaPayload(
            $this->temaRepository->obtenerGeneros(),
            $this->complementarioService->getGeneros()
        );

        $caracterizaciones = $this->buildTemaPayload(
            $this->temaRepository->obtenerCaracterizacionesComplementarias()
        );

        $vias = $this->buildTemaPayload($this->temaRepository->obtenerVias());
        $letras = $this->buildTemaPayload($this->temaRepository->obtenerLetras());
        $cardinales = $this->buildTemaPayload($this->temaRepository->obtenerCardinales());

        $paises = Pais::all();
        $departamentos = Departamento::all();
        $municipios = collect();

        $categoriasConHijos = $this->obtenerCaracterizacionesAgrupadas($caracterizaciones);

        $personaAutenticada = Auth::check() ? Auth::user()->persona : null;

        return [
            'programa' => $programa,
            'categoriasConHijos' => $categoriasConHijos,
            'paises' => $paises,
            'departamentos' => $departamentos,
            'municipios' => $municipios,
            'documentos' => $documentos,
            'generos' => $generos,
            'caracterizaciones' => $caracterizaciones,
            'vias' => $vias,
            'letras' => $letras,
            'cardinales' => $cardinales,
            'personaAutenticada' => $personaAutenticada,
        ];
    }

    /**
     * Procesar inscripción a programa específico
     */
    public function procesarInscripcion(array $data, int $programaId): RedirectResponse
    {
        try {
            // Verificar si el usuario ya está inscrito
            if ($this->verificarInscripcionExistente($programaId)) {
                return redirect()->back()->with('error', 'Ya estás inscrito en este programa complementario.');
            }

            return DB::transaction(function () use ($data, $programaId) {
                // Procesar persona
                $persona = $this->procesarPersona($data);

                // Procesar usuario
                $this->procesarUsuario($data, $persona);

                // Crear aspirante
                $aspirante = $this->crearAspirante($persona, $programaId, $data);

                // Procesar documento
                $this->procesarDocumento($data, $aspirante, $persona);

                return redirect()->route('login.index')->with(
                    'success',
                    '¡Inscripción completada exitosamente! Su cuenta de usuario ha sido creada. ' .
                    'Puede iniciar sesión con su correo electrónico y número de documento como contraseña.'
                );
            });

        } catch (\Exception $e) {
            Log::error('Error en inscripción a programa: ' . $e->getMessage(), [
                'programa_id' => $programaId,
                'data' => $data,
                'exception' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al procesar su inscripción. Por favor intente nuevamente.');
        }
    }

    /**
     * Verificar si el usuario ya está inscrito en el programa
     */
    private function verificarInscripcionExistente(int $programaId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->aspiranteRepository->existeInscripcion(Auth::user()->persona_id, $programaId);
    }

    /**
     * Procesar datos de persona (crear o actualizar)
     */
    private function procesarPersona(array $data): Persona
    {
        // Convertir parametro_id a parametros_temas.id
        $data = $this->convertirParametrosAParametrosTemas($data);

        return $this->personaRepository->createOrUpdate($data);
    }

    /**
     * Convertir parametro_id a parametros_temas.id para tipo_documento y genero
     */
    private function convertirParametrosAParametrosTemas(array $data): array
    {
        // Convertir tipo_documento (parametro_id) a parametros_temas.id
        if (isset($data['tipo_documento'])) {
            $parametroTema = \App\Models\ParametroTema::where('tema_id', 2) // TIPO DE DOCUMENTO
                ->where('parametro_id', $data['tipo_documento'])
                ->first();

            if ($parametroTema) {
                $data['tipo_documento'] = $parametroTema->id;
            }
        }

        // Convertir genero (parametro_id) a parametros_temas.id
        if (isset($data['genero'])) {
            $parametroTema = \App\Models\ParametroTema::where('tema_id', 3) // GENERO
                ->where('parametro_id', $data['genero'])
                ->first();

            if ($parametroTema) {
                $data['genero'] = $parametroTema->id;
            }
        }

        return $data;
    }

    /**
     * Procesar usuario (crear o actualizar rol)
     */
    private function procesarUsuario(array $data, Persona $persona): void
    {
        $this->userService->createOrUpdateForAspirante($data, $persona);
    }

    /**
     * Crear registro de aspirante
     */
    private function crearAspirante(Persona $persona, int $programaId, array $data): AspiranteComplementario
    {
        return $this->aspiranteRepository->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programaId,
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 1, // Estado "En proceso"
        ]);
    }

    /**
     * Procesar documento de identidad
     */
    private function procesarDocumento(array $data, AspiranteComplementario $aspirante, Persona $persona): void
    {
        if (!isset($data['documento_identidad'])) {
            return;
        }

        try {
            $file = $data['documento_identidad'];
            $fileName = $this->generarNombreArchivo($persona, $file);

            Log::info('Subiendo archivo a Google Drive', [
                'file_name' => $fileName,
                'aspirante_id' => $aspirante->id
            ]);

            $path = Storage::disk('google')->putFileAs('documentos_aspirantes', $file, $fileName);

            $this->aspiranteRepository->update($aspirante, [
                'documento_identidad_path' => $path,
                'documento_identidad_nombre' => $fileName,
                'estado' => 2, // Estado "Completo"
            ]);

            Log::info('Documento procesado exitosamente', [
                'aspirante_id' => $aspirante->id,
                'path' => $path
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar documento: ' . $e->getMessage(), [
                'aspirante_id' => $aspirante->id,
                'exception' => $e->getTraceAsString()
            ]);

            // Mantener aspirante en estado "En proceso"
            $this->aspiranteRepository->update($aspirante, ['estado' => 1]);

            throw new ProcesarDocumentoIdentidadException('Error al procesar el documento de identidad');
        }
    }

    /**
     * Generar nombre único para el archivo
     */
    private function generarNombreArchivo(Persona $persona, $file): string
    {
        $tipoDocumento = $persona->tipoDocumento->name ?? 'DOC';
        $numeroDocumento = $persona->numero_documento;
        $primerNombre = $persona->primer_nombre;
        $primerApellido = $persona->primer_apellido;
        $timestamp = now()->format('d-m-y-H-i-s');
        $extension = $file->getClientOriginalExtension();

        return "{$tipoDocumento}_{$numeroDocumento}_{$primerNombre}_{$primerApellido}_{$timestamp}.{$extension}";
    }

    /**
     * Obtener caracterizaciones agrupadas
     */
    private function obtenerCaracterizacionesAgrupadas(?object $caracterizacionesPayload = null): Collection
    {
        $payload = $caracterizacionesPayload ?? $this->buildTemaPayload(
            $this->temaRepository->obtenerCaracterizacionesComplementarias()
        );

        $parametros = collect($payload->parametros ?? []);

        if ($parametros->isEmpty()) {
            return collect();
        }

        $hijos = $parametros->map(function ($parametro) {
            $id = data_get($parametro, 'id');
            $nombre = data_get($parametro, 'name', data_get($parametro, 'nombre', ''));

            if (!$id) {
                return null;
            }

            $formatted = (string) Str::of($nombre ?? '')
                ->replace('_', ' ')
                ->lower()
                ->title();

            return (object) [
                'id' => $id,
                'nombre' => $formatted,
            ];
        })->filter()->values();

        if ($hijos->isEmpty()) {
            return collect();
        }

        return collect([
            [
                'id' => $payload->id ?? null,
                'nombre' => 'Opciones disponibles',
                'hijos' => $hijos,
            ],
        ]);
    }

    /**
     * Construir payload de tema
     */
    private function buildTemaPayload($tema = null, $fallback = null): object
    {
        if ($tema && $tema->parametros?->count()) {
            return $tema;
        }

        $parametros = $this->normalizeParametrosCollection($fallback);

        return (object) [
            'parametros' => $parametros,
        ];
    }

    /**
     * Normalizar colección de parámetros
     */
    private function normalizeParametrosCollection($items): Collection
    {
        return collect($items)->map(function ($item) {
            $id = data_get($item, 'id');
            $name = data_get($item, 'name', data_get($item, 'nombre', ''));

            if ($id === null) {
                return null;
            }

            return (object) [
                'id' => $id,
                'name' => strtoupper((string) $name),
            ];
        })->filter()->values();
    }
}
