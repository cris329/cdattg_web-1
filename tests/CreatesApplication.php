<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Forzar SQLite para tests (sobrescribe .env)
        if (env('APP_ENV') === 'testing') {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', database_path('testing.sqlite'));
            
            // Asegurar que el archivo SQLite existe
            $databasePath = database_path('testing.sqlite');
            if (!file_exists($databasePath)) {
                touch($databasePath);
            }
        }

        return $app;
    }
}
