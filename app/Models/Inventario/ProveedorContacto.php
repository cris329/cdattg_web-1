<?php

namespace App\Models\Inventario;

use App\Traits\Seguimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProveedorContacto extends Model
{
    use HasFactory;
    use Seguimiento;

    protected $table = 'proveedor_contactos';

    protected $fillable = [
        'proveedor_id',
        'nombre',
        'telefono',
        'email',
        'user_create_id',
        'user_update_id'
    ];

    /**
     * Relación con el proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
