<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Update
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $proveedor = $this->route('proveedor');
            $proveedorId = is_object($proveedor) ? $proveedor->id : $proveedor;
            
            return [
                'name' => [
                    'required',
                    Rule::unique('proveedores', 'name')->ignore($proveedorId),
                ],
                'nit' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('proveedores', 'nit')->ignore($proveedorId),
                ],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('proveedores', 'email')->ignore($proveedorId),
                ],
                'telefono' => 'nullable|string|max:10',
                'direccion' => 'nullable|string|max:255',
                'pais_id' => 'nullable|exists:pais,id',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'municipio_id' => 'nullable|exists:municipios,id',
                'persona_id' => 'nullable|exists:personas,id',
                'estado_id' => 'nullable|exists:parametros_temas,id'
            ];
        }

        // Store
        return [
            'name' => 'required|unique:proveedores,name',
            'nit' => 'nullable|string|max:50|unique:proveedores,nit',
            'email' => 'nullable|email|max:255|unique:proveedores,email',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'nullable|string|max:255',
            'pais_id' => 'nullable|exists:pais,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'municipio_id' => 'nullable|exists:municipios,id',
            'persona_id' => 'nullable|exists:personas,id',
            'estado_id' => 'nullable|exists:parametros_temas,id'
        ];
    }
}
