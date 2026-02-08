<?php

namespace App\Livewire\Asistencia;

use App\Models\Asistencia;
use App\Models\Instructor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaConsultaIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $roleNames = $user?->getRoleNames() ?? collect();
        $isOnlyInstructor = $user && $user->hasRole('INSTRUCTOR') && $roleNames->count() === 1;

        $instructorId = null;
        if ($isOnlyInstructor && $user?->persona_id) {
            $instructorId = Instructor::where('persona_id', $user->persona_id)->value('id');
        }

        $asistencias = Asistencia::query()
            ->with([
                'evidencia',
                'instructorFicha.programaFormacion',
                'instructorFicha.instructor.persona',
            ])
            ->withCount('asistenciaAprendices')
            ->when($isOnlyInstructor, function ($query) use ($instructorId) {
                if (!$instructorId) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                $query->whereHas('instructorFicha', function ($q) use ($instructorId) {
                    $q->where(function ($sub) use ($instructorId) {
                        $sub->where('instructor_id', $instructorId)
                            ->orWhereHas('instructorFicha', function ($aux) use ($instructorId) {
                                $aux->where('instructor_id', $instructorId);
                            });
                    });
                });
            })
            ->when($this->search, function ($query) {
                $search = $this->search;

                $query->where(function ($q) use ($search) {
                    $q->whereHas('instructorFicha', function ($sub) use ($search) {
                        $sub->where('ficha', 'like', '%' . $search . '%')
                            ->orWhereHas('programaFormacion', function ($p) use ($search) {
                                $p->where('nombre', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instructor.persona', function ($i) use ($search) {
                                $i->where('numero_documento', 'like', '%' . $search . '%')
                                    ->orWhere('primer_nombre', 'like', '%' . $search . '%')
                                    ->orWhere('primer_apellido', 'like', '%' . $search . '%');
                            });
                    });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.asistencia.asistencia-consulta-index', [
            'asistencias' => $asistencias,
        ]);
    }
}
