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
            return redirect()->route('login')->with('message', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        });

        $this->renderable(function (Throwable $e, $request) {
            // Ignorar peticiones JSON/API o de recursos estáticos/Livewire
            if ($request->expectsJson() || $request->is('api/*') || $request->is('livewire/*')) {
                return null;
            }

            $currentHost = $request->getHost();
            $mainUrl = rtrim(config('modules.admin_url', 'http://172.17.50.250'), '/');
            $mainHost = parse_url($mainUrl, PHP_URL_HOST);

            // Si estamos en un contenedor secundario (.251 o .252) y ocurre un fallo de base de datos o error fatal 500
            if ($currentHost !== $mainHost && !str_contains($currentHost, '250')) {
                if ($e instanceof QueryException || $e instanceof \PDOException || (!config('app.debug') && method_exists($e, 'getStatusCode') && $e->getStatusCode() >= 500)) {
                    return redirect("{$mainUrl}/inicio")->with('error', 'El módulo experimentó un problema técnico temporal. Has sido retornado al contenedor principal.');
                }
            }

            return null;
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
