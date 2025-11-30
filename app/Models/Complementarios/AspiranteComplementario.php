<?php

namespace App\Models\Complementarios;

use App\Models\Persona;
use Database\Factories\AspiranteComplementarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspiranteComplementario extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return AspiranteComplementarioFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'aspirantes_complementarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'persona_id',
        'complementario_id',
        'observaciones',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estado' => 'integer',
    ];

    /**
     * Get the persona associated with the aspirant.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Get the complementario associated with the aspirant.
     */
    public function complementario()
    {
        return $this->belongsTo(ComplementarioOfertado::class, 'complementario_id');
    }

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getEstadoLabelAttribute()
    {
        return match($this->estado) {
            1 => 'En proceso',
            2 => 'Completo',
            3 => 'Admitido',
            4 => 'Rechazado',
            default => 'Desconocido'
        };
    }
}
