<?php

namespace App\Exceptions;

use RuntimeException;

class ParametroTemaCreationException extends RuntimeException
{
    public function __construct(int $temaId, int $parametroId, ?string $additionalMessage = null)
    {
        $message = "No se pudo crear el parametro_tema para tema_id={$temaId} y parametro_id={$parametroId}";
        if ($additionalMessage) {
            $message .= ": {$additionalMessage}";
        }
        parent::__construct($message);
    }
}

