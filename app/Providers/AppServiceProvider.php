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
            \App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface::class,
            \App\Inventario\Repositories\Categoria\CategoriaRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface::class,
            \App\Inventario\Repositories\Proveedor\ProveedorRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface::class,
            \App\Inventario\Repositories\Orden\OrdenRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Devolucion\DevolucionRepositoryInterface::class,
            \App\Inventario\Repositories\Devolucion\DevolucionRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface::class,
            \App\Inventario\Repositories\Marca\MarcaRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface::class,
            \App\Inventario\Repositories\ContratoConvenio\ContratoConvenioRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Aprobacion\AprobacionRepositoryInterface::class,
            \App\Inventario\Repositories\Aprobacion\AprobacionRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface::class,
            \App\Inventario\Repositories\Orden\DetalleOrdenRepository::class
        );

        // Bindings de servicios de Inventario (SOLID - DIP)
        $this->app->bind(
            \App\Inventario\Interfaces\Services\UserRepositoryInterface::class,
            \App\Inventario\Repositories\User\UserRepository::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Services\NotificationServiceInterface::class,
            \App\Inventario\Services\Notification\NotificationService::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Services\ImageServiceInterface::class,
            \App\Inventario\Services\Image\ImageService::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Services\BarcodeServiceInterface::class,
            \App\Inventario\Services\Barcode\BarcodeService::class
        );

        // Nuevos servicios SOLID
        $this->app->bind(
            \App\Inventario\Interfaces\Services\FormOptionsServiceInterface::class,
            \App\Inventario\Services\FormOptions\FormOptionsService::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Services\StockValidatorServiceInterface::class,
            \App\Inventario\Services\StockValidator\StockValidatorService::class
        );

        $this->app->bind(
            \App\Inventario\Interfaces\Services\TransactionServiceInterface::class,
            \App\Inventario\Services\Transaction\TransactionService::class
        );

        // Servicio de enriquecimiento de productos (singleton para performance)
        $this->app->singleton(
            \App\Inventario\Services\ProductoEnrichment\ProductoEnrichmentService::class
        );

        // Bindings de notificaciones
        $this->app->bind(
            \App\Inventario\Interfaces\Repositories\Notification\NotificationRepositoryInterface::class,
            \App\Inventario\Repositories\Notification\NotificationRepository::class
        );

        $this->app->bind(
            \App\Inventario\Services\Notification\UserNotificationService::class
        );

        // Servicio de devoluciones
        $this->app->bind(
            \App\Inventario\Services\Devolucion\DevolucionService::class
        );

        // Servicio de datos de formularios (ContratoConvenio, Ambiente, Proveedor)
        $this->app->bind(
            \App\Inventario\Services\FormData\FormDataService::class
        );

        // Servicio de productos
        $this->app->bind(
            \App\Inventario\Services\Producto\ProductoService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        setlocale(LC_ALL, 'es_ES', 'es', 'ES', 'es_ES.utf8');
        \Carbon\Carbon::setLocale(config('app.locale', 'es'));
        date_default_timezone_set(config('app.timezone'));
        Schema::defaultStringLength(191);
        PaginationPaginator::useBootstrap();

        // Registrar observadores
        AsistenciaAprendiz::observe(AsistenciaAprendizObserver::class);

        // Cargar migraciones de subdirectorios
        $migrationsPath = database_path('migrations');
        $directories = glob($migrationsPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $this->loadMigrationsFrom($directory);
        }

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
