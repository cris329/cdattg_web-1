<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Ejecuta las migraciones para los tests usando el sistema de migraciones modulares.
     * Esto asegura que las migraciones se ejecuten en el orden correcto según las dependencias.
     *
     * @return void
     */
    protected function migrateDatabases()
    {
        // Primero ejecutar migrate:fresh para limpiar la base de datos
        $this->artisan('migrate:fresh');
        
        // Luego ejecutar todas las migraciones modulares en orden
        $this->artisan('migrate:module', ['--all' => true]);
    }
}
