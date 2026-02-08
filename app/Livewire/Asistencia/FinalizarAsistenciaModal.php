<?php

namespace App\Livewire\Asistencia;

use Livewire\Component;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Log;

class FinalizarAsistenciaModal extends Component
{
    public $showFinalizarModal = false;
    public $asistencia;
    public $asistenciaId;

    protected $listeners = [
        'openFinalizarModal' => 'openFinalizarModal',
        'closeFinalizarModal' => 'closeFinalizarModal'
    ];

    public function mount($asistenciaId = null)
    {
        $this->asistenciaId = $asistenciaId;
        
        if ($asistenciaId) {
            $this->asistencia = Asistencia::find($asistenciaId);
        }
    }

    public function openFinalizarModal()
    {
        // Recargar asistencia para obtener estado actual
        if ($this->asistenciaId) {
            $this->asistencia = Asistencia::find($this->asistenciaId);
        }
        
        if (!$this->asistencia) {
            session()->flash('error', 'Asistencia no encontrada.');
            return;
        }

        if ($this->asistencia->is_finished) {
            session()->flash('warning', 'La asistencia ya fue finalizada.');
            return;
        }

        $this->showFinalizarModal = true;
    }

    public function closeFinalizarModal()
    {
        $this->showFinalizarModal = false;
    }

    public function finalizarAsistencia()
    {
        // Validar que exista la asistencia
        if (!$this->asistencia) {
            session()->flash('error', 'Asistencia no encontrada.');
            $this->closeFinalizarModal();
            return;
        }

        // Verificar que no esté ya finalizada
        if ($this->asistencia->is_finished) {
            session()->flash('warning', 'La asistencia ya fue finalizada.');
            $this->closeFinalizarModal();
            return;
        }

        try {
            // Finalizar la asistencia
            $this->asistencia->update([
                'is_finished' => true,
                'hora_fin' => now(),
                'user_edit_id' => auth()->id()
            ]);

            Log::info('Asistencia finalizada exitosamente:', [
                'asistencia_id' => $this->asistencia->id,
                'hora_fin' => $this->asistencia->hora_fin,
                'user_id' => auth()->id()
            ]);

            session()->flash('success', 'Asistencia finalizada correctamente.');
            
            // Cerrar modal
            $this->closeFinalizarModal();
            
            // Redirigir a la lista de asistencias
            return redirect()->route('asistencias.index');

        } catch (\Exception $e) {
            Log::error('Error al finalizar asistencia:', [
                'asistencia_id' => $this->asistencia->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Error al finalizar la asistencia: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.asistencia.finalizar-asistencia-modal');
    }
}
