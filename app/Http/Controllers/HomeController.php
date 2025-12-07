<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complementarios\ComplementarioOfertado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener programas complementarios activos (estado = 1)
        // Nota: estado_id ahora es FK a parametros_temas, necesitamos obtener el ID correspondiente
        $estadoActivoId = $this->getEstadoIdByLegacyValue(1);
        
        $programas = ComplementarioOfertado::with(['modalidad.parametro', 'jornada', 'diasFormacion']);
        
        if ($estadoActivoId) {
            $programas = $programas->where('estado_id', $estadoActivoId);
        } else {
            // Si no se encuentra el estado_id, retornar colección vacía
            $programas = $programas->where('estado_id', 0); // Esto no devolverá resultados
        }
        
        $programas = $programas->get();

        // Asignar iconos a cada programa
        $programas->each(function($programa) {
            $programa->icono = $this->getIconoForPrograma($programa->nombre);
        });

        // Obtener programas en los que el usuario está inscrito
        $programasInscritos = collect();
        $programasInscritosIds = collect();

        if (Auth::check() && Auth::user()->persona) {
            $personaId = Auth::user()->persona->id;

            // Debug: Verificar si hay datos en la tabla aspirantes_complementarios
            $aspirantesCount = \App\Models\Complementarios\AspiranteComplementario::where('persona_id', $personaId)
                ->where('estado', 1)
                ->count();

            Log::info("Debug HomeController - Persona ID: {$personaId}, Aspirantes encontrados: {$aspirantesCount}");

            $programasInscritos = ComplementarioOfertado::with(['modalidad.parametro', 'jornada', 'diasFormacion'])
                ->whereHas('aspirantes', function($query) use ($personaId) {
                    $query->where('persona_id', $personaId)
                          ->where('estado', 1); // Estado 1 = En proceso
                })
                ->get();

            Log::info("Debug HomeController - Programas inscritos encontrados: " . $programasInscritos->count());

            // Obtener IDs de programas inscritos
            $programasInscritosIds = $programasInscritos->pluck('id');

            // Asignar iconos a cada programa inscrito
            $programasInscritos->each(function($programa) {
                $programa->icono = $this->getIconoForPrograma($programa->nombre);
            });
        } else {
            Log::info("Debug HomeController - Usuario no autenticado o sin persona asociada");
        }

        return view('home', compact('programas', 'programasInscritos', 'programasInscritosIds'));
    }

    /**
     * Obtener el estado_id correspondiente a un valor legacy (0,1,2)
     */
    private function getEstadoIdByLegacyValue(int $estadoLegacy): ?int
    {
        $nombreEstado = match ($estadoLegacy) {
            0 => 'Sin Oferta',
            1 => 'Con Oferta',
            2 => 'Cupos Llenos',
            default => 'Sin Oferta',
        };
        
        // Buscar el ParametroTema correspondiente al estado
        try {
            $temaEstado = \App\Models\Tema::where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO')->first();
            
            if ($temaEstado) {
                $parametro = \App\Models\Parametro::where('name', $nombreEstado)->first();
                
                if ($parametro) {
                    $parametroTema = \App\Models\ParametroTema::where('tema_id', $temaEstado->id)
                        ->where('parametro_id', $parametro->id)
                        ->first();
                    
                    if ($parametroTema) {
                        return $parametroTema->id;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error, retornar null
            Log::error("Error obteniendo estado_id para valor legacy {$estadoLegacy}: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Obtener icono para programa basado en su nombre
     */
    private function getIconoForPrograma($nombrePrograma)
    {
        $iconos = [
            'Auxiliar de Cocina' => 'fas fa-utensils',
            'Acabados en Madera' => 'fas fa-hammer',
            'Confección de Prendas' => 'fas fa-cut',
            'Mecánica Básica Automotriz' => 'fas fa-car',
            'Cultivos de Huertas Urbanas' => 'fas fa-spa',
            'Normatividad Laboral' => 'fas fa-gavel',
        ];

        return $iconos[$nombrePrograma] ?? 'fas fa-graduation-cap';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
