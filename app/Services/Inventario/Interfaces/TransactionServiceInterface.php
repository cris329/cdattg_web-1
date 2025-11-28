<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

interface TransactionServiceInterface
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
    public function transaction(callable $callback);
}

