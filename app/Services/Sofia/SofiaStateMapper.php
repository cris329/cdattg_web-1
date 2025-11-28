<?php

namespace App\Services\Sofia;

use Illuminate\Support\Facades\Log;

class SofiaStateMapper
{
    /**
     * Mapear resultado de validación a estado Sofia
     * 
     * @param string $resultado Resultado de la validación
     * @return int Estado Sofia (0: No registrado, 1: Registrado, 2: Requiere cambio)
     */
    public function mapToState(string $resultado): int
    {
        $resultadoLower = strtolower($resultado);

        // PRIMERO: Verificar si es error
        if ($resultado === 'ERROR' || str_contains($resultadoLower, 'error')) {
            Log::warning("Error en validación de SenaSofiaPlus: '{$resultado}'");
            return 0; // No registrado - error se trata como no registrado
        }

        // SEGUNDO: Verificar respuestas directas del script
        if ($resultado === 'YA_EXISTE') {
            return 1; // Registrado
        }

        if ($resultado === 'NO_REGISTRADO') {
            return 0; // No registrado
        }

        if ($resultado === 'REQUIERE_CAMBIO') {
            return 2; // Requiere cambio de cédula
        }

        if ($resultado === 'DESCONOCIDO') {
            return 0; // No registrado
        }

        // TERCERO: Verificar si requiere cambio de documento (texto largo)
        if (str_contains($resultadoLower, 'requiere_cambio') ||
            str_contains($resultadoLower, 'actualizar tu documento') ||
            str_contains($resultadoLower, 'cambiar tu documento') ||
            str_contains($resultadoLower, 'tarjeta de identidad')) {
            return 2; // Requiere cambio de cédula
        }

        // CUARTO: Verificar si está registrado correctamente (texto largo)
        if (str_contains($resultadoLower, 'ya existe') ||
            str_contains($resultadoLower, 'ya cuentas con un registro') ||
            str_contains($resultadoLower, 'cuenta registrada')) {
            return 1; // Registrado
        }

        // QUINTO: Verificar si NO está registrado (puede registrarse)
        if (str_contains($resultadoLower, 'no_registrado') ||
            str_contains($resultadoLower, 'desconocido') ||
            trim($resultado) === '') {
            return 0; // No registrado - puede crear cuenta
        }

        // SEXTO: Cualquier otro resultado se considera como no registrado
        Log::warning("Respuesta no reconocida de SenaSofiaPlus: '{$resultado}' - tratando como no registrado");
        return 0; // Por defecto, asumir no registrado
    }

    /**
     * Obtener etiqueta legible del estado
     */
    public function getStateLabel(int $estado): string
    {
        return match($estado) {
            0 => 'No registrado',
            1 => 'Registrado',
            2 => 'Requiere cambio',
            default => 'Desconocido'
        };
    }
}

