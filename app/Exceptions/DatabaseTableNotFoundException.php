<?php

namespace App\Exceptions;

use RuntimeException;

class DatabaseTableNotFoundException extends RuntimeException
{
    public function __construct(string $tableName)
    {
        parent::__construct("La tabla '{$tableName}' no existe. Ejecute las migraciones primero.");
    }
}
