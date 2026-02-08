<?php

namespace App\Livewire\Asistencia;

use Livewire\Component;
use App\Models\FichaCaracterizacion;
use App\Models\Evidencias;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrearEvidenciaModal extends Component
{
    public $showModalEvidencia = false;
    public $nombreEvidencia = '';
    public $selectedFicha = null;
    public $selectedFichaId = null;

    protected $rules = [
        'nombreEvidencia' => 'required|min:3|max:255'
    ];

    protected $messages = [
        'nombreEvidencia.required' => 'El nombre de la evidencia es obligatorio.',
        'nombreEvidencia.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombreEvidencia.max' => 'El nombre no puede superar 255 caracteres.',
    ];

    protected $listeners = ['openModalEvidencia'];

    public function openModalEvidencia($fichaId)
    {
        try {
            Log::info('=== VERIFICANDO ASISTENCIA ACTIVA ===');
            Log::info('Ficha ID: ' . $fichaId);
            
            // 1. Buscar si ya existe una asistencia activa para esta ficha
            $asistenciaActiva = Asistencia::where('instructor_ficha_id', $fichaId)
                ->where('is_finished', false)
                ->first();
            
            if ($asistenciaActiva) {
                Log::info('Asistencia activa encontrada: ' . $asistenciaActiva->id);
                // Redirigir directamente a la asistencia existente
                return $this->redirect(route('asistence.caracterSelected', [
                    'caracterizacion' => $fichaId,
                    'asistencia_id' => $asistenciaActiva->id
                ]));
            }
            
            Log::info('No hay asistencia activa, abriendo modal para crear evidencia');
            
            // 2. Si no hay asistencia activa, abrir modal para crear evidencia
            $this->selectedFichaId = $fichaId;
            $this->selectedFicha = FichaCaracterizacion::with([
                'programaFormacion',
                'instructor.persona'
            ])->find($fichaId);
            
            if (!$this->selectedFicha) {
                Log::error('Ficha no encontrada: ' . $fichaId);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Ficha no encontrada'
                ]);
                return;
            }
            
            $this->reset(['nombreEvidencia']);
            $this->showModalEvidencia = true;
            
            Log::info('Modal abierto correctamente para nueva evidencia');
            
        } catch (\Exception $e) {
            Log::error('Error al verificar asistencia activa: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al verificar asistencia activa: ' . $e->getMessage()
            ]);
        }
    }

    public function closeModalEvidencia()
    {
        $this->showModalEvidencia = false;
        // No limpiar selectedFichaId ni selectedFicha porque se necesitan para la redirección
        $this->reset(['nombreEvidencia']);
    }

    public function crearEvidencia()
    {
        try {
            Log::info('=== CREANDO EVIDENCIA Y ASISTENCIA ===');
            Log::info('Datos recibidos:', [
                'nombreEvidencia' => $this->nombreEvidencia,
                'fichaId' => $this->selectedFichaId,
                'userId' => Auth::id()
            ]);

            // Validar datos
            $this->validate();

            // 1. Crear la evidencia
            $evidencia = Evidencias::create([
                'nombre' => $this->nombreEvidencia,
                'user_create_id' => Auth::id(),
                'user_edit_id' => Auth::id(),
                'fecha_evidencia' => now()->toDateString(),
                'id_estado' => 1 // Activo
            ]);

            Log::info('Evidencia creada exitosamente:', [
                'evidencia_id' => $evidencia->id,
                'nombre' => $evidencia->nombre,
                'fecha' => $evidencia->fecha_evidencia
            ]);

            // 2. Crear la asistencia asociada
            $asistencia = Asistencia::create([
                'evidencia_id' => $evidencia->id,
                'instructor_ficha_id' => $this->selectedFichaId,
                'fecha' => now()->toDateString(),
                'hora_inicio' => now(),
                'is_finished' => false,
                'user_create_id' => Auth::id(),
                'user_edit_id' => Auth::id()
            ]);

            Log::info('Asistencia creada exitosamente:', [
                'asistencia_id' => $asistencia->id,
                'evidencia_id' => $asistencia->evidencia_id,
                'instructor_ficha_id' => $asistencia->instructor_ficha_id,
                'fecha' => $asistencia->fecha,
                'hora_inicio' => $asistencia->hora_inicio,
                'user_create_id' => $asistencia->user_create_id,
                'user_edit_id' => $asistencia->user_edit_id,
                'is_finished' => $asistencia->is_finished
            ]);

            // Cerrar modal
            $this->closeModalEvidencia();

            // Notificar éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Evidencia y asistencia creadas correctamente. Redirigiendo...'
            ]);

            // Redirigir a la vista de tomar asistencia con la asistencia creada
            Log::info('Datos para redirección:', [
                'selectedFichaId' => $this->selectedFichaId,
                'asistencia_id' => $asistencia->id,
                'evidencia_id' => $evidencia->id
            ]);
            
            return $this->redirect(route('asistence.caracterSelected', [
                'caracterizacion' => $this->selectedFichaId,
                'asistencia_id' => $asistencia->id
            ]));

        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar error de duplicado
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                Log::warning('Nombre de evidencia duplicado: ' . $this->nombreEvidencia);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Ya existe una evidencia con este nombre. Por favor, usa un nombre diferente.'
                ]);
            } else {
                Log::error('Error de base de datos al crear evidencia/asistencia: ' . $e->getMessage());
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error de base de datos: ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al crear evidencia/asistencia: ' . $e->getMessage());
            Log::error('Stack trace:', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la evidencia y asistencia: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.asistencia.crear-evidencia-modal');
    }
}
