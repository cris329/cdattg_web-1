<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class MarcaCategoriaRequest extends FormRequest
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
        // Update - el route parameter puede ser 'categoria' o 'marca'
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $parametroId = $this->obtenerParametroId();
            return [
                'name' => 'required|string|unique:parametros,name,' . $parametroId,
            ];
        }

        // Store
        return [
            'name' => 'required|string|unique:parametros,name',
        ];
    }

    private function obtenerParametroId(): ?int
    {
        $categoria = $this->route('categoria');
        if ($categoria) {
            return $categoria->id;
        }

        $marca = $this->route('marca');
        if ($marca) {
            return $marca->id;
        }

        return null;
    }
}
