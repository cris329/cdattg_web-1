<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Transaction\TransactionService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class TransactionServiceTest extends TestCase
{
    protected TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TransactionService();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(TransactionService::class, $this->service);
    }

    #[Test]
    public function puede_iniciar_transaccion(): void
    {
        $nivelInicial = DB::transactionLevel();

        $this->service->beginTransaction();

        $this->assertGreaterThan($nivelInicial, DB::transactionLevel());

        DB::rollBack();
    }

    #[Test]
    public function puede_hacer_commit(): void
    {
        DB::beginTransaction();
        
        $this->service->commit();

        $this->assertEquals(0, DB::transactionLevel());
    }

    #[Test]
    public function puede_hacer_rollback(): void
    {
        DB::beginTransaction();
        
        $this->service->rollBack();

        $this->assertEquals(0, DB::transactionLevel());
    }

    #[Test]
    public function puede_ejecutar_transaccion(): void
    {
        $resultado = $this->service->transaction(function () {
            return 'resultado';
        });

        $this->assertEquals('resultado', $resultado);
        $this->assertEquals(0, DB::transactionLevel());
    }

    #[Test]
    public function hace_rollback_si_transaccion_falla(): void
    {
        try {
            $this->service->transaction(function () {
                throw new \Exception('Error de prueba');
            });
        } catch (\Exception $e) {
            $this->assertEquals(0, DB::transactionLevel());
        }
    }
}
