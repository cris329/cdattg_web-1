<?php

declare(strict_types=1);

namespace Tests\Inventario\Unit\Services;

use Tests\TestCase;
use App\Inventario\Services\Notification\UserNotificationService;
use App\Inventario\Interfaces\Repositories\Notification\NotificationRepositoryInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class UserNotificationServiceTest extends TestCase
{
    protected UserNotificationService $service;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(NotificationRepositoryInterface::class);

        $this->service = new UserNotificationService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function servicio_puede_instanciarse(): void
    {
        $this->assertInstanceOf(UserNotificationService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_notificaciones_paginadas(): void
    {
        $userId = 1;
        $perPage = 10;

        $paginatorMock = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);

        $this->mockRepository->shouldReceive('obtenerPorUsuarioPaginadas')
            ->once()
            ->with($userId, $perPage)
            ->andReturn($paginatorMock);

        $resultado = $this->service->obtenerNotificacionesPaginadas($userId, $perPage);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $resultado);
    }

    #[Test]
    public function usa_per_page_por_defecto_si_no_se_proporciona(): void
    {
        $userId = 1;
        $perPageDefault = config('inventario.notificaciones.per_page', 10);

        $paginatorMock = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);

        $this->mockRepository->shouldReceive('obtenerPorUsuarioPaginadas')
            ->once()
            ->with($userId, $perPageDefault)
            ->andReturn($paginatorMock);

        $resultado = $this->service->obtenerNotificacionesPaginadas($userId);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $resultado);
    }

    #[Test]
    public function puede_obtener_notificaciones_no_leidas(): void
    {
        $userId = 1;
        $limit = 5;

        $collectionMock = collect([]);

        $this->mockRepository->shouldReceive('obtenerNoLeidasLimitadas')
            ->once()
            ->with($userId, $limit)
            ->andReturn($collectionMock);

        $resultado = $this->service->obtenerNoLeidas($userId, $limit);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultado);
    }

    #[Test]
    public function usa_limit_por_defecto_si_no_se_proporciona(): void
    {
        $userId = 1;
        $limitDefault = config('inventario.notificaciones.dropdown_limit', 5);

        $collectionMock = collect([]);

        $this->mockRepository->shouldReceive('obtenerNoLeidasLimitadas')
            ->once()
            ->with($userId, $limitDefault)
            ->andReturn($collectionMock);

        $resultado = $this->service->obtenerNoLeidas($userId);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultado);
    }

    #[Test]
    public function puede_contar_notificaciones_no_leidas(): void
    {
        $userId = 1;
        $count = 3;

        $this->mockRepository->shouldReceive('contarNoLeidas')
            ->once()
            ->with($userId)
            ->andReturn($count);

        $resultado = $this->service->contarNoLeidas($userId);

        $this->assertEquals($count, $resultado);
        $this->assertIsInt($resultado);
    }

    #[Test]
    public function puede_marcar_como_leida(): void
    {
        $userId = 1;
        $notificationId = '123';

        $this->mockRepository->shouldReceive('marcarComoLeida')
            ->once()
            ->with($userId, $notificationId)
            ->andReturn(true);

        $resultado = $this->service->marcarComoLeida($userId, $notificationId);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_marcar_todas_como_leidas(): void
    {
        $userId = 1;
        $count = 5;

        $this->mockRepository->shouldReceive('marcarTodasComoLeidas')
            ->once()
            ->with($userId)
            ->andReturn($count);

        $resultado = $this->service->marcarTodasComoLeidas($userId);

        $this->assertEquals($count, $resultado);
        $this->assertIsInt($resultado);
    }

    #[Test]
    public function puede_eliminar_notificacion(): void
    {
        $userId = 1;
        $notificationId = '123';

        $this->mockRepository->shouldReceive('eliminar')
            ->once()
            ->with($userId, $notificationId)
            ->andReturn(true);

        $resultado = $this->service->eliminar($userId, $notificationId);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function puede_obtener_datos_dropdown(): void
    {
        $userId = 1;
        $limit = config('inventario.notificaciones.dropdown_limit', 5);
        $count = 3;

        $collectionMock = collect([]);

        $this->mockRepository->shouldReceive('obtenerNoLeidasLimitadas')
            ->once()
            ->with($userId, $limit)
            ->andReturn($collectionMock);

        $this->mockRepository->shouldReceive('contarNoLeidas')
            ->once()
            ->with($userId)
            ->andReturn($count);

        $resultado = $this->service->obtenerDatosDropdown($userId);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('notificaciones', $resultado);
        $this->assertArrayHasKey('count', $resultado);
        $this->assertEquals($count, $resultado['count']);
    }
}
