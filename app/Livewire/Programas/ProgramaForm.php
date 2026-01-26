<?php

namespace App\Livewire\Programas;

use Livewire\Component;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use App\Models\Parametro;
use App\Http\Requests\StoreProgramaFormacionRequest;
use App\Http\Requests\UpdateProgramaFormacionRequest;
use Livewire\Attributes\Validate;

class ProgramaForm extends Component
{
    public $programaId = null;
    public $isEdit = false;

    #[Validate('required|string|max:6')]
    public $codigo = '';

    #[Validate('required|string|max:255')]
    public $nombre = '';

    #[Validate('required|exists:red_conocimientos,id')]
    public $red_conocimiento_id = '';

    #[Validate('required|exists:parametros,id')]
    public $nivel_formacion_id = '';

    #[Validate('required|integer|min:1')]
    public $horas_totales = '';

    #[Validate('required|integer|min:1')]
    public $horas_etapa_lectiva = '';

    #[Validate('required|integer|min:1')]
    public $horas_etapa_productiva = '';

    // Estado para validación visual
    public bool $horas_validas = false;

    protected $listeners = [
        'editPrograma' => 'loadPrograma',
    ];

    public function updated($property)
    {
        // Validar horas cuando cambian los campos relacionados
        if (in_array($property, [
            'horas_totales',
            'horas_etapa_lectiva',
            'horas_etapa_productiva'
        ])) {
            $this->horas_validas = 
                ($this->horas_etapa_lectiva + $this->horas_etapa_productiva)
                === $this->horas_totales;
        }
    }

    public function mount($programaId = null)
    {
        if ($programaId) {
            $this->isEdit = true;
            $this->loadPrograma($programaId);
        }
    }

    public function loadPrograma($programaId)
    {
        $programa = ProgramaFormacion::find($programaId);
        if ($programa) {
            $this->programaId = $programa->id;
            $this->isEdit = true;
            $this->codigo = (string) $programa->codigo;
            $this->nombre = $programa->nombre;
            $this->red_conocimiento_id = $programa->red_conocimiento_id;
            $this->nivel_formacion_id = $programa->nivel_formacion_id;
            $this->horas_totales = $programa->horas_totales;
            $this->horas_etapa_lectiva = $programa->horas_etapa_lectiva;
            $this->horas_etapa_productiva = $programa->horas_etapa_productiva;
        }
    }

    public function save()
    {
        if ($this->isEdit) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function store()
    {
        try {
            // Asegurar que el código sea string
            $this->codigo = (string) $this->codigo;
            
            // VALIDACIÓN SIMPLE - Solo usar rules()
            $validated = $this->validate();
            
            // Validación manual de unicidad del código
            $existingPrograma = ProgramaFormacion::where('codigo', $validated['codigo'])->first();
                
            if ($existingPrograma) {
                $this->addError('codigo', 'El código ya está siendo utilizado por otro programa.');
                return;
            }
            
            // Validación de negocio simple
            if (($validated['horas_etapa_lectiva'] + $validated['horas_etapa_productiva']) != $validated['horas_totales']) {
                $this->addError('horas_totales', 'La suma de horas lectiva y productiva debe coincidir con el total.');
                return;
            }
            
            // FORZAR ESTADO ACTIVO POR DEFECTO
            $validated['status'] = true;
            $validated['user_create_id'] = auth()->id();
            
            $programa = ProgramaFormacion::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Programa creado correctamente',
            ]);
            
            $this->dispatch('programaCreado');
            $this->reset();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el programa: ' . $e->getMessage(),
            ]);
        }
    }

    public function update()
    {
        try {
            // Asegurar que el código sea string
            $this->codigo = (string) $this->codigo;
            
            // VALIDACIÓN SIMPLE - Solo usar rules()
            $validated = $this->validate();
            
            // Validación manual de unicidad del código
            $existingPrograma = ProgramaFormacion::where('codigo', $validated['codigo'])
                ->where('id', '!=', $this->programaId)
                ->first();
                
            if ($existingPrograma) {
                $this->addError('codigo', 'El código ya está siendo utilizado por otro programa.');
                return;
            }
            
            // Validación de negocio simple
            if (($validated['horas_etapa_lectiva'] + $validated['horas_etapa_productiva']) != $validated['horas_totales']) {
                $this->addError('horas_totales', 'La suma de horas lectiva y productiva debe coincidir con el total.');
                return;
            }
            
            $programa = ProgramaFormacion::find($this->programaId);
            if (!$programa) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Programa no encontrado',
                ]);
                return;
            }
            
            // Mantener el estado actual del programa (no forzar a true)
            $validated['user_edit_id'] = auth()->id();
            
            $programa->update($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Programa actualizado correctamente',
            ]);
            
            $this->dispatch('programaActualizado');
            $this->reset();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el programa: ' . $e->getMessage(),
            ]);
        }
    }

    public function cancel()
    {
        // Solo cerrar el modal sin hacer reset para evitar parpadeo
        $this->dispatch('closeModal');
    }

    public function getRedesConocimientoProperty()
    {
        return RedConocimiento::all();
    }

    public function getNivelesFormacionProperty()
    {
        return Parametro::whereIn('name', ['TÉCNICO', 'TECNÓLOGO', 'AUXILIAR', 'OPERARIO'])->get();
    }

    public function render()
    {
        return view('livewire.programas.programa-form');
    }

    protected function rules()
    {
        $rules = [
            'codigo' => 'required|string|max:6|regex:/^[0-9]+$/|unique:programas_formacion,codigo',
            'nombre' => 'required|string|max:255',
            'red_conocimiento_id' => 'required|exists:red_conocimientos,id',
            'nivel_formacion_id' => 'required|exists:parametros,id',
            'horas_totales' => 'required|integer|min:1|max:20000',
            'horas_etapa_lectiva' => 'required|integer|min:1|max:20000',
            'horas_etapa_productiva' => 'required|integer|min:1|max:20000',
        ];

        if ($this->isEdit && $this->programaId) {
            $rules['codigo'] = 'required|string|max:6|regex:/^[0-9]+$/|unique:programas_formacion,codigo,' . $this->programaId . ',id';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'codigo.regex' => 'El código debe contener solo números (0-9).',
            'codigo.unique' => 'El código ya está siendo utilizado por otro programa.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede tener más de 6 caracteres.',
            'nombre.required' => 'El nombre del programa es obligatorio.',
            'red_conocimiento_id.required' => 'Debe seleccionar una red de conocimiento.',
            'nivel_formacion_id.required' => 'Debe seleccionar un nivel de formación.',
            'horas_totales.required' => 'Las horas totales son obligatorias.',
            'horas_totales.max' => 'Las horas totales no pueden superar las 20,000 horas.',
            'horas_etapa_lectiva.required' => 'Las horas lectivas son obligatorias.',
            'horas_etapa_lectiva.max' => 'Las horas lectivas no pueden superar las 20,000 horas.',
            'horas_etapa_productiva.required' => 'Las horas productivas son obligatorias.',
            'horas_etapa_productiva.max' => 'Las horas productivas no pueden superar las 20,000 horas.',
        ];
    }
}
