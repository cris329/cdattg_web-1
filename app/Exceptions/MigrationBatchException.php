<?php

namespace App\Exceptions;

use RuntimeException;

class MigrationBatchException extends RuntimeException
{
    public function __construct(string $batch)
    {
        parent::__construct("Error al migrar el batch: {$batch}");
    }
}

