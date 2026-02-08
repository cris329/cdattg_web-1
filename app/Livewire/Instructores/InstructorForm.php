<?php

namespace App\Livewire\Instructores;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\Regional;
use App\Models\CentroFormacion;
use App\Models\RedConocimiento;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Services\InstructorService;
use App\Services\InstructorBusinessRulesService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InstructorForm extends Component
{
    // Propiedades básicas
    public $isEdit = false;
    public $instructor = null;
    
    // Selección de persona
    public $persona_id = null;
    
    // Información laboral
    public $regional_id = null;
    public $centro_formacion_id = null;
    public $tipo_vinculacion_id = null;
    public $jornadas = [];
    public $fecha_ingreso_sena = null;
    public $anos_experiencia = null;
    public $experiencia_instructor_meses = null;
    public $experiencia_laboral = null;
    
    // Formación académica
    public $nivel_academico_id = null;
    public $formacion_pedagogia = null;
    public $titulos_obtenidos = [''];
    public $instituciones_educativas = [''];
    public $certificaciones_tecnicas = [''];
    public $cursos_complementarios = [''];
    
    // Competencias y habilidades
    public $areas_experticia = [''];
    public $competencias_tic = [''];
    public $idiomas = [['idioma' => '', 'nivel' => '']];
    public $modalidades = []; // Habilidades pedagógicas
    public $especialidades = [];
    
    // Información administrativa
    public $numero_contrato = null;
    public $fecha_inicio_contrato = null;
    public $fecha_fin_contrato = null;
    public $supervisor_contrato = null;
    
    // Datos para selects
    public $personasDisponibles = [];
    public $regionales = [];
    public $centrosFormacion = [];
    public $tiposVinculacion = [];
    public $jornadasTrabajo = [];
    public $nivelesAcademicos = [];
    public $redesConocimiento = [];
    public $modalidadesDisponibles = [];

    protected $instructorService;
    protected $businessRulesService;

    protected $listeners = [
        'closeModal' => 'handleCloseModal',
        'refreshComponent' => '$refresh',
    ];

    public function boot(InstructorService $instructorService, InstructorBusinessRulesService $businessRulesService)
    {
        $this->instructorService = $instructorService;
        $this->businessRulesService = $businessRulesService;
    }

    public function mount($instructor = null, $isEdit = false)
    {
        $this->isEdit = $isEdit;
        
        if ($instructor && $isEdit) {
            $this->instructor = $instructor;
            $this->loadInstructorData();
        }
        
        $this->cargarDatosSelects();
    }

    private function cargarDatosSelects()
    {
        // Personas disponibles: no tienen rol de instructor ni registro en instructors
        $this->personasDisponibles = Persona::query()
            ->whereDoesntHave('instructor')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->whereRaw('model_has_roles.model_id = personas.id')
                    ->where('roles.name', 'INSTRUCTOR');
            })
            ->when($this->isEdit && $this->instructor, function($query) {
                $query->orWhere('id', $this->instructor->persona_id);
            })
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        // Regionales
        $this->regionales = Regional::where('status', true)
            ->orderBy('nombre')
            ->get();

        // Centros de formación
        $this->centrosFormacion = CentroFormacion::where('status', true)
            ->orderBy('nombre')
            ->get();

        // Jornadas de trabajo
        $this->jornadasTrabajo = ParametroTema::where('tema_id', 23) // Tema JORNADAS
            ->where('status', true)
            ->with('parametro')
            ->get()
            ->sortBy(function($pt) {
                return $pt->parametro->name ?? '';
            })
            ->values();

        // Tipos de vinculación
        $this->tiposVinculacion = ParametroTema::where('tema_id', 24) // Tema TIPOS DE VINCULACION
            ->where('status', true)
            ->with('parametro')
            ->get()
            ->sortBy(function($pt) {
                return $pt->parametro->name;
            })
            ->values();

        // Niveles académicos
        $this->nivelesAcademicos = ParametroTema::where('tema_id', 25) // Tema NIVELES ACADEMICOS
            ->where('status', true)
            ->with('parametro')
            ->get()
            ->sortBy(function($pt) {
                return $pt->parametro->name;
            })
            ->values();

        // Redes de conocimiento (especialidades)
        $this->redesConocimiento = RedConocimiento::where('status', true)
            ->orderBy('nombre')
            ->get();

        // Modalidades (habilidades pedagógicas)
        $this->modalidadesDisponibles = ParametroTema::where('tema_id', 5) // Tema MODALIDADES DE FORMACION
            ->where('status', true)
            ->with('parametro')
            ->get()
            ->sortBy(function($pt) {
                return $pt->parametro->name;
            })
            ->values();
    }

    private function loadInstructorData()
    {
        if (!$this->instructor) return;

        // Datos básicos
        $this->persona_id = $this->instructor->persona_id;
        $this->regional_id = $this->instructor->regional_id;
        $this->centro_formacion_id = $this->instructor->centro_formacion_id;
        $this->tipo_vinculacion_id = $this->instructor->tipo_vinculacion_id;
        $this->jornadas = $this->instructor->jornadas ?? [];
        
        // Fechas - Formatear para input type="date" (Y-m-d)
        $this->fecha_ingreso_sena = $this->instructor->fecha_ingreso_sena ? 
            $this->instructor->fecha_ingreso_sena->format('Y-m-d') : null;
        
        $this->anos_experiencia = $this->instructor->anos_experiencia;
        $this->experiencia_instructor_meses = $this->instructor->experiencia_instructor_meses;
        $this->experiencia_laboral = $this->instructor->experiencia_laboral;

        // Formación académica
        $this->nivel_academico_id = $this->instructor->nivel_academico_id;
        $this->formacion_pedagogia = $this->instructor->formacion_pedagogia;
        $this->titulos_obtenidos = $this->instructor->titulos_obtenidos ?? [''];
        $this->instituciones_educativas = $this->instructor->instituciones_educativas ?? [''];
        $this->certificaciones_tecnicas = $this->instructor->certificaciones_tecnicas ?? [''];
        $this->cursos_complementarios = $this->instructor->cursos_complementarios ?? [''];

        // Competencias y habilidades
        $this->areas_experticia = $this->instructor->areas_experticia ?? [''];
        $this->competencias_tic = $this->instructor->competencias_tic ?? [''];
        $this->idiomas = $this->instructor->idiomas ?? [['idioma' => '', 'nivel' => '']];
        $this->modalidades = $this->instructor->modalidades ?? [];
        $this->especialidades = $this->instructor->especialidades ?? [];

        // Información administrativa
        $this->numero_contrato = $this->instructor->numero_contrato;
        
        // Fechas de contrato - Formatear para input type="date" (Y-m-d)
        $this->fecha_inicio_contrato = $this->instructor->fecha_inicio_contrato ? 
            $this->instructor->fecha_inicio_contrato->format('Y-m-d') : null;
        
        $this->fecha_fin_contrato = $this->instructor->fecha_fin_contrato ? 
            $this->instructor->fecha_fin_contrato->format('Y-m-d') : null;
        
        $this->supervisor_contrato = $this->instructor->supervisor_contrato;
    }

    // Reglas de validación
    protected function rules()
    {
        $rules = [
            // Persona (requerido solo en creación)
            'persona_id' => $this->isEdit ? 'required|exists:personas,id' : 'required|exists:personas,id',
            
            // Información laboral
            'regional_id' => 'required|exists:regionals,id',
            'centro_formacion_id' => 'nullable|exists:centro_formacions,id',
            'tipo_vinculacion_id' => 'nullable|exists:parametros_temas,id',
            'jornadas' => 'array',
            'jornadas.*' => 'exists:parametros_temas,id',
            'fecha_ingreso_sena' => 'nullable|date',
            'anos_experiencia' => 'nullable|integer|min:0|max:50',
            'experiencia_instructor_meses' => 'nullable|integer|min:0|max:600',
            'experiencia_laboral' => 'nullable|string|max:1000',
            
            // Formación académica
            'nivel_academico_id' => 'nullable|exists:parametros_temas,id',
            'formacion_pedagogia' => 'nullable|string|max:1000',
            'titulos_obtenidos.*' => 'nullable|string|max:200',
            'instituciones_educativas.*' => 'nullable|string|max:200',
            'certificaciones_tecnicas.*' => 'nullable|string|max:200',
            'cursos_complementarios.*' => 'nullable|string|max:200',
            
            // Competencias y habilidades
            'areas_experticia.*' => 'nullable|string|max:200',
            'competencias_tic.*' => 'nullable|string|max:200',
            'idiomas.*.idioma' => 'nullable|string|max:100',
            'idiomas.*.nivel' => 'nullable|string|max:50',
            'modalidades' => 'array',
            'modalidades.*' => 'exists:parametros_temas,id',
            'especialidades' => 'array',
            
            // Información administrativa
            'numero_contrato' => 'nullable|string|max:50',
            'fecha_inicio_contrato' => 'nullable|date',
            'fecha_fin_contrato' => 'nullable|date|after_or_equal:fecha_inicio_contrato',
            'supervisor_contrato' => 'nullable|string|max:200',
        ];

        return $rules;
    }

    protected function validationAttributes()
    {
        return [
            'persona_id' => 'persona',
            'regional_id' => 'regional',
            'centro_formacion_id' => 'centro de formación',
            'tipo_vinculacion_id' => 'tipo de vinculación',
            'jornadas.*' => 'jornada',
            'nivel_academico_id' => 'nivel académico',
            'titulos_obtenidos.*' => 'título obtenido',
            'instituciones_educativas.*' => 'institución educativa',
            'certificaciones_tecnicas.*' => 'certificación técnica',
            'cursos_complementarios.*' => 'curso complementario',
            'areas_experticia.*' => 'área de experticia',
            'competencias_tic.*' => 'competencia TIC',
            'idiomas.*.idioma' => 'idioma',
            'idiomas.*.nivel' => 'nivel de idioma',
            'modalidades.*' => 'modalidad',
            'especialidades.principal' => 'especialidad principal',
            'especialidades.secundarias.*' => 'especialidad secundaria',
        ];
    }

    public function save()
    {
        try {
            \Log::info('[InstructorForm] Intentando guardar instructor', [
                'isEdit' => $this->isEdit,
                'persona_id' => $this->persona_id,
                'regional_id' => $this->regional_id,
                // Puedes agregar más campos si lo necesitas
            ]);

            $this->validate();

            // Verifica si hay personas disponibles antes de crear
            if (!$this->isEdit && ($this->personasDisponibles->isEmpty() || !$this->persona_id)) {
                \Log::warning('[InstructorForm] No hay personas disponibles para crear instructor.', [
                    'personasDisponibles' => $this->personasDisponibles,
                    'persona_id' => $this->persona_id,
                ]);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No hay personas disponibles para crear instructor.',
                ]);
                return;
            }

            // Preparar datos para el servicio
            $datos = $this->prepareDataForService();
            \Log::info('[InstructorForm] Datos preparados para guardar', $datos);

            if ($this->isEdit) {
                // Actualizar instructor existente
                $this->instructorService->actualizar($this->instructor->id, $datos);

                \Log::info('[InstructorForm] Instructor actualizado correctamente', [
                    'instructor_id' => $this->instructor->id,
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Instructor actualizado correctamente',
                ]);
                $this->dispatch('instructorActualizado');
            } else {
                // Crear nuevo instructor
                $instructor = $this->instructorService->crear($datos, $this->jornadas);

                \Log::info('[InstructorForm] Instructor creado correctamente', [
                    'instructor_id' => $instructor->id ?? null,
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Instructor creado correctamente',
                ]);
                $this->dispatch('instructorCreado');
            }

            // Cerrar modal
            $this->dispatch('closeModal');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('[InstructorForm] Error de validación', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error de validación: ' . $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            \Log::error('[InstructorForm] Error guardando instructor', [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar instructor: ' . $e->getMessage(),
            ]);
        }
    }

    private function prepareDataForService()
    {
        $datos = [
            // Información básica
            'persona_id' => $this->persona_id,
            'regional_id' => $this->regional_id,
            'centro_formacion_id' => $this->centro_formacion_id,
            'tipo_vinculacion_id' => $this->tipo_vinculacion_id,
            'jornadas' => $this->jornadas,
            'fecha_ingreso_sena' => $this->fecha_ingreso_sena,
            'anos_experiencia' => $this->anos_experiencia,
            'experiencia_instructor_meses' => $this->experiencia_instructor_meses,
            'experiencia_laboral' => $this->experiencia_laboral,
            
            // Formación académica
            'nivel_academico_id' => $this->nivel_academico_id,
            'formacion_pedagogia' => $this->formacion_pedagogia,
            'titulos_obtenidos' => $this->cleanArray($this->titulos_obtenidos),
            'instituciones_educativas' => $this->cleanArray($this->instituciones_educativas),
            'certificaciones_tecnicas' => $this->cleanArray($this->certificaciones_tecnicas),
            'cursos_complementarios' => $this->cleanArray($this->cursos_complementarios),
            
            // Competencias y habilidades
            'areas_experticia' => $this->cleanArray($this->areas_experticia),
            'competencias_tic' => $this->cleanArray($this->competencias_tic),
            'idiomas' => $this->cleanIdiomasArray($this->idiomas),
            'modalidades' => $this->modalidades,
            'especialidades' => $this->especialidades,
            
            // Información administrativa
            'numero_contrato' => $this->numero_contrato,
            'fecha_inicio_contrato' => $this->fecha_inicio_contrato,
            'fecha_fin_contrato' => $this->fecha_fin_contrato,
            'supervisor_contrato' => $this->supervisor_contrato,
            
            // Usuario
            'user_create_id' => Auth::id(),
            'user_edit_id' => Auth::id(),
        ];

        return $datos;
    }

    private function cleanArray($array)
    {
        return array_values(array_filter(array_map('trim', $array), function($value) {
            return $value !== '' && $value !== null;
        }));
    }

    private function cleanIdiomasArray($idiomas)
    {
        return array_values(array_filter($idiomas, function($idioma) {
            return !empty($idioma['idioma']) && !empty($idioma['nivel']);
        }));
    }

    // Métodos para manejar campos dinámicos
    public function addCampo($campo)
    {
        switch($campo) {
            case 'titulos_obtenidos':
            case 'instituciones_educativas':
            case 'certificaciones_tecnicas':
            case 'cursos_complementarios':
            case 'areas_experticia':
            case 'competencias_tic':
                $this->{$campo}[] = '';
                break;
            case 'idiomas':
                $this->idiomas[] = ['idioma' => '', 'nivel' => ''];
                break;
        }
    }

    public function removeCampo($campo, $index)
    {
        if (isset($this->{$campo}[$index])) {
            unset($this->{$campo}[$index]);
            $this->{$campo} = array_values($this->{$campo});
        }
    }

    public function handleCloseModal()
    {
        // Este método es llamado cuando el modal se cierra desde el componente padre
    }

    // Métodos para obtener nombres de relaciones
    public function getRegionalNombreProperty()
    {
        if ($this->regional_id) {
            $regional = Regional::find($this->regional_id);
            return $regional ? $regional->nombre : '';
        }
        return '';
    }

    public function getCentroFormacionNombreProperty()
    {
        if ($this->centro_formacion_id) {
            $centro = CentroFormacion::find($this->centro_formacion_id);
            return $centro ? $centro->nombre : '';
        }
        return '';
    }

    // Métodos para especialidades
    public function updatedEspecialidades()
    {
        // Asegurar que el array de especialidades tenga la estructura correcta
        if (!is_array($this->especialidades)) {
            $this->especialidades = [];
        }
        
        if (!isset($this->especialidades['principal'])) {
            $this->especialidades['principal'] = null;
        }
        
        if (!isset($this->especialidades['secundarias'])) {
            $this->especialidades['secundarias'] = [];
        }
    }

    public function render()
    {
        return view('livewire.instructores.instructor-form');
    }
}
