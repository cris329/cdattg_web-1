<?php

namespace App\Livewire\GuiasAprendizaje;

use Livewire\Component;
use App\Models\GuiasAprendizaje;
use App\Models\ResultadosAprendizaje;

class GestionarRelacionesHandler extends Component
{
    public $guia;
    public $resultadosDisponibles;
    public $resultadosAsignados;
    public $searchDisponible = '';
    public $searchAsignado = '';

    protected $listeners = [
        'confirmAction' => 'handleConfirmAction',
        'refreshComponent' => '$refresh',
    ];

    public function mount(GuiasAprendizaje $guia)
    {
        $this->guia = $guia;
        $this->cargarResultados();
    }

    public function cargarResultados()
    {
        // Cargar resultados disponibles (no asignados a esta guía)
        $queryDisponibles = ResultadosAprendizaje::whereDoesntHave('guiaAprendizajes', function ($query) {
            $query->where('guia_aprendizajes.id', $this->guia->id);
        });

        if ($this->searchDisponible) {
            $queryDisponibles->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->searchDisponible . '%')
                  ->orWhere('nombre', 'like', '%' . $this->searchDisponible . '%');
            });
        }

        $this->resultadosDisponibles = $queryDisponibles->orderBy('codigo')->get();

        // Cargar resultados asignados a esta guía
        $queryAsignados = $this->guia->resultadosAprendizaje();

        if ($this->searchAsignado) {
            $queryAsignados->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->searchAsignado . '%')
                  ->orWhere('nombre', 'like', '%' . $this->searchAsignado . '%');
            });
        }

        $this->resultadosAsignados = $queryAsignados->orderBy('codigo')->get();
    }

    public function updatedSearchDisponible()
    {
        $this->cargarResultados();
    }

    public function updatedSearchAsignado()
    {
        $this->cargarResultados();
    }

    public function asignarResultado($resultadoId)
    {
        try {
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Resultado de aprendizaje no encontrado',
                ]);
                return;
            }

            // Verificar si ya está asignado
            if ($this->guia->resultadosAprendizaje()->where('resultados_aprendizaje.id', $resultadoId)->exists()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'El resultado ya está asignado a esta guía',
                ]);
                return;
            }

            // Asignar resultado a la guía
            $this->guia->resultadosAprendizaje()->attach($resultadoId, [
                'user_create_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Resultado '{$resultado->codigo}' asignado correctamente",
            ]);

            $this->cargarResultados();
            $this->dispatch('refreshComponent');

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al asignar resultado: ' . $e->getMessage(),
            ]);
        }
    }

    public function desasociarResultado($resultadoId)
    {
        try {
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Resultado de aprendizaje no encontrado',
                ]);
                return;
            }

            // Verificar si está asignado
            if (!$this->guia->resultadosAprendizaje()->where('resultados_aprendizaje.id', $resultadoId)->exists()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'El resultado no está asignado a esta guía',
                ]);
                return;
            }

            // Desasociar resultado de la guía
            $this->guia->resultadosAprendizaje()->detach($resultadoId);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Resultado '{$resultado->codigo}' desasociado correctamente",
            ]);

            $this->cargarResultados();
            $this->dispatch('refreshComponent');

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al desasociar resultado: ' . $e->getMessage(),
            ]);
        }
    }

    public function handleConfirmAction($data)
    {
        $action = $data['action'] ?? '';
        $params = $data['params'] ?? [];

        switch ($action) {
            case 'asignarResultado':
                $this->asignarResultado($params);
                break;
            case 'desasociarResultado':
                $this->desasociarResultado($params);
                break;
        }
    }

    public function render()
    {
        return view('livewire.guias-aprendizaje.gestionar-relaciones-handler');
    }
}
