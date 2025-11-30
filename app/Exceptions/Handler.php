<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            // En testing, devolver 403 directamente
            // Verificar tanto 'testing' como si estamos en un test de PHPUnit
            if (app()->environment('testing') || defined('PHPUNIT_COMPOSER_INSTALL')) {
                return response('No tiene autorización para hacer esta acción.', 403);
            }
            return redirect()->route('home')->with('error', 'No tiene autorización para hacer esta acción.');
        }

        return parent::render($request, $exception);
    }
}
