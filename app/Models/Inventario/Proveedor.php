<?php

namespace App\Models\Inventario;

use App\Traits\Seguimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proveedor extends Model
{
    use HasFactory;
    use Seguimiento;

    protected $table = 'proveedores';

    protected static function booted()
    {
        static::creating(function ($proveedor) {
            $proveedor->name = strtoupper($proveedor->name);
        });

        static::updating(function ($proveedor) {
            $proveedor->name = strtoupper($proveedor->name);
        });
    }

    protected $fillable = [
        'name',
        'nit',
        'email',
        'telefono',
        'direccion',
        'pais_id',
        'departamento_id',
        'municipio_id',
        'estado_id',
        'persona_id',
        'user_create_id',
        'user_update_id'
    ];

    // Relación con el estado
    public function estado() : BelongsTo
    {
        return $this->belongsTo(\App\Models\ParametroTema::class, 'estado_id');
    }

    // Relación con el país
    public function pais() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Pais::class);
    }

    // Relación con el departamento
    public function departamento() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Departamento::class);
    }

    // Relación con el municipio
    public function municipio() : BelongsTo
    {
        return $this->belongsTo(\App\Models\Municipio::class);
    }

    // Relación con contratos y convenios
    public function contratosConvenios() : HasMany
    {
        return $this->hasMany(ContratoConvenio::class);
    }

    // Relación con productos
    public function productos() : HasMany
    {
        return $this->hasMany(Producto::class);
    }

    // Relación con persona (contacto)
    public function persona(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Persona::class);
    }
}
