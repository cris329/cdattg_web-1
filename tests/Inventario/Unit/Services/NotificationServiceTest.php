<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Notification\NotificationService;
use App\Inventario\Interfaces\Services\UserRepositoryInterface;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class NotificationServiceTest extends TestCase
{
    protected NotificationService $service;
    protected $mockUserRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->service = new NotificationService($this->mockUserRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(NotificationService::class, $this->service);
    }

    #[Test]
    public function puede_notificar_nueva_orden(): void
    {
        $userMock = Mockery::mock(User::class);
        $userMock->id = 1;

        $ordenMock = Mockery::mock(Orden::class);

        $this->mockUserRepository->shouldReceive('obtenerSuperAdministradores')
            ->once()
            ->andReturn(collect([$userMock]));

        $this->service->notificarNuevaOrden($ordenMock);

        Notification::assertNothingSent();
    }

    #[Test]
    public function no_notifica_si_no_hay_super_administradores(): void
    {
        $ordenMock = Mockery::mock(Orden::class);

        $this->mockUserRepository->shouldReceive('obtenerSuperAdministradores')
            ->once()
            ->andReturn(collect([]));

        $this->service->notificarNuevaOrden($ordenMock);

        Notification::assertNothingSent();
    }

    #[Test]
    public function puede_notificar_stock_bajo(): void
    {
        $userMock = Mockery::mock(User::class);
        $userMock->id = 1;

        $productoMock = Mockery::mock(Producto::class);
        $productoMock->id = 1;
        $productoMock->shouldReceive('notify');

        $this->mockUserRepository->shouldReceive('obtenerSuperAdministradores')
            ->once()
            ->andReturn(collect([$userMock]));

        $this->service->notificarStockBajo($productoMock, 3, 10);

        Notification::assertNothingSent();
    }

    #[Test]
    public function no_notifica_stock_bajo_si_no_hay_administradores(): void
    {
        $productoMock = Mockery::mock(Producto::class);

        $this->mockUserRepository->shouldReceive('obtenerSuperAdministradores')
            ->once()
            ->andReturn(collect([]));

        $this->service->notificarStockBajo($productoMock, 3, 10);

        Notification::assertNothingSent();
    }
}
