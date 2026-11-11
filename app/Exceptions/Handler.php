<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        });

        // Manejar expiración de token CSRF (Error 419) y redirigir limpiamente al login
        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión o token de seguridad ha expirado. Por favor, recarga la página e inicia sesión nuevamente.',
                    'csrf_expired' => true
                ], 419);
            }
            return redirect()->route('login')->with('message', 'Tu sesión ha caducado por inactividad o seguridad. Por favor, inicia sesión nuevamente.');
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
