<?php

namespace App\Providers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\Paginator as PaginationPaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\AsistenciaAprendiz;
use App\Observers\AsistenciaAprendizObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bindings de repositorios de Inventario
        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface::class,
            \App\Inventario\Repositories\Producto\ProductoRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\CategoriaRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\CategoriaRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\ProveedorRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\ProveedorRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\OrdenRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\OrdenRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\DevolucionRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\DevolucionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\MarcaRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\MarcaRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\ContratoConvenioRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\ContratoConvenioRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\AprobacionRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\AprobacionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Inventario\DetalleOrdenRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\DetalleOrdenRepository::class
        );

        // Bindings de servicios de Inventario (SOLID - DIP)
        $this->app->bind(
            \App\Services\Inventario\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\Inventario\UserRepository::class
        );

        $this->app->bind(
            \App\Services\Inventario\Interfaces\NotificationServiceInterface::class,
            \App\Services\Inventario\NotificationService::class
        );

        $this->app->bind(
            \App\Services\Inventario\Interfaces\ImageServiceInterface::class,
            \App\Services\Inventario\ImageService::class
        );

        $this->app->bind(
            \App\Services\Inventario\Interfaces\BarcodeServiceInterface::class,
            \App\Services\Inventario\BarcodeService::class
        );

        // Nuevos servicios SOLID
        $this->app->bind(
            \App\Services\Inventario\Interfaces\FormOptionsServiceInterface::class,
            \App\Services\Inventario\FormOptionsService::class
        );

        $this->app->bind(
            \App\Services\Inventario\Interfaces\StockValidatorServiceInterface::class,
            \App\Services\Inventario\StockValidatorService::class
        );

        $this->app->bind(
            \App\Services\Inventario\Interfaces\TransactionServiceInterface::class,
            \App\Services\Inventario\TransactionService::class
        );

        // Servicio de enriquecimiento de productos (singleton para performance)
        $this->app->singleton(
            \App\Services\Inventario\ProductoEnrichmentService::class
        );

        // Bindings de notificaciones
        $this->app->bind(
            \App\Repositories\Interfaces\Notificaciones\NotificationRepositoryInterface::class,
            \App\Repositories\Eloquent\Notificaciones\NotificationRepository::class
        );

        $this->app->bind(
            \App\Services\Notificaciones\UserNotificationService::class
        );

        // Servicio de devoluciones
        $this->app->bind(
            \App\Services\Inventario\DevolucionService::class
        );

        // Servicio de datos de formularios (ContratoConvenio, Ambiente, Proveedor)
        $this->app->bind(
            \App\Services\Inventario\FormDataService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        setlocale(LC_ALL, 'es_ES', 'es', 'ES', 'es_ES.utf8');
        \Carbon\Carbon::setLocale(config('app.locale', 'es'));
        date_default_timezone_set(config('app.timezone'));
        Schema::defaultStringLength(191);
        PaginationPaginator::useBootstrap();

        // Registrar observadores
        AsistenciaAprendiz::observe(AsistenciaAprendizObserver::class);

        // Registrar driver de Google Drive
        try {
            Storage::extend('google', function ($config) {
                $options = [];

                if (!empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);

                // Establecer scopes explícitos para evitar 403 "forbidden" por permisos insuficientes
                if (class_exists(\Google\Service\Drive::class)) {
                    $client->setScopes([\Google\Service\Drive::DRIVE_FILE, \Google\Service\Drive::DRIVE]);
                } else {
                    $driveFileScope = 'https://www.googleapis.com/auth/drive.file';
                    $driveScope = 'https://www.googleapis.com/auth/drive';
                    $client->setScopes([$driveFileScope, $driveScope]);
                }
                $client->setAccessType('offline');
                if (method_exists($client, 'setIncludeGrantedScopes')) {
                    $client->setIncludeGrantedScopes(true);
                }

                // Usar refresh token configurado para obtener/renovar el access token
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter(
                    $service,
                    $config['folderId'] ?? '/',
                    $options
                );
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            Log::error('Error al registrar driver de Google Drive: ' . $e->getMessage());
        }
    }
}
