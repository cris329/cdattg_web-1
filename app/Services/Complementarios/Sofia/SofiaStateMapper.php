<?php

namespace App\Services\Complementarios\Sofia;

use Illuminate\Support\Facades\Log;

class SofiaStateMapper
{
    // IDs de parámetros según ParametroSeeder
    private const ESTADO_NO_REGISTRADO = 277;
    private const ESTADO_REGISTRADO = 278;
    private const ESTADO_REQUIERE_CAMBIO = 279;

    private const RESULTADO_ERROR = 'ERROR';
    private const RESULTADO_YA_EXISTE = 'YA_EXISTE';
    private const RESULTADO_NO_REGISTRADO = 'NO_REGISTRADO';
    private const RESULTADO_REQUIERE_CAMBIO = 'REQUIERE_CAMBIO';
    private const RESULTADO_DESCONOCIDO = 'DESCONOCIDO';

    private const PATRONES_REQUIERE_CAMBIO = [
        'requiere_cambio',
        'actualizar tu documento',
        'cambiar tu documento',
        'tarjeta de identidad'
    ];

    private const PATRONES_REGISTRADO = [
        'ya existe',
        'ya cuentas con un registro',
        'cuenta registrada'
    ];

    private const PATRONES_NO_REGISTRADO = [
        'no_registrado',
        'desconocido'
    ];

    /**
     * Mapear resultado de validación a estado Sofia
     *
     * @param string $resultado Resultado de la validación
     * @return int Estado Sofia (277: No registrado, 278: Registrado, 279: Requiere cambio)
     */
    public function mapToState(string $resultado): int
    {
        $resultadoLower = strtolower($resultado);
        $estado = null;

        if ($this->isError($resultado, $resultadoLower)) {
            $estado = self::ESTADO_NO_REGISTRADO;
        } elseif (($directState = $this->getDirectState($resultado)) !== null) {
            $estado = $directState;
        } elseif ($this->requiresChange($resultadoLower)) {
            $estado = self::ESTADO_REQUIERE_CAMBIO;
        } elseif ($this->isRegistered($resultadoLower)) {
            $estado = self::ESTADO_REGISTRADO;
        } elseif ($this->isNotRegistered($resultadoLower, $resultado)) {
            $estado = self::ESTADO_NO_REGISTRADO;
        }

        if ($estado === null) {
            Log::warning('Respuesta no reconocida de SenaSofiaPlus', ['resultado' => $resultado]);
            $estado = self::ESTADO_NO_REGISTRADO;
        }

        return $estado;
    }

    /**
     * Obtener etiqueta legible del estado
     */
    public function getStateLabel(int $estado): string
    {
        return match($estado) {
            self::ESTADO_NO_REGISTRADO => 'No registrado',
            self::ESTADO_REGISTRADO => 'Registrado',
            self::ESTADO_REQUIERE_CAMBIO => 'Requiere cambio',
            default => 'Desconocido'
        };
    }

    /**
     * Verificar si es error
     */
    private function isError(string $resultado, string $resultadoLower): bool
    {
        if ($resultado === self::RESULTADO_ERROR || str_contains($resultadoLower, 'error')) {
            Log::warning('Error en validacion de SenaSofiaPlus', ['resultado' => $resultado]);
            return true;
        }
        return false;
    }

    /**
     * Obtener estado directo del resultado
     */
    private function getDirectState(string $resultado): ?int
    {
        $resultadoUpper = strtoupper($resultado);

        return match($resultadoUpper) {
            self::RESULTADO_YA_EXISTE => self::ESTADO_REGISTRADO,
            self::RESULTADO_NO_REGISTRADO, self::RESULTADO_DESCONOCIDO => self::ESTADO_NO_REGISTRADO,
            self::RESULTADO_REQUIERE_CAMBIO => self::ESTADO_REQUIERE_CAMBIO,
            default => null
        };
    }

    /**
     * Verificar si requiere cambio
     */
    private function requiresChange(string $resultadoLower): bool
    {
        foreach (self::PATRONES_REQUIERE_CAMBIO as $patron) {
            if (str_contains($resultadoLower, $patron)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si está registrado
     */
    private function isRegistered(string $resultadoLower): bool
    {
        foreach (self::PATRONES_REGISTRADO as $patron) {
            if (str_contains($resultadoLower, $patron)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si no está registrado
     */
    private function isNotRegistered(string $resultadoLower, string $resultado): bool
    {
        foreach (self::PATRONES_NO_REGISTRADO as $patron) {
            if (str_contains($resultadoLower, $patron)) {
                return true;
            }
        }
        return trim($resultado) === '';
    }
}

