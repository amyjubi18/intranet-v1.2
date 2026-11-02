<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

class ExceptionController extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException && $exception->getStatusCode() == 403) {
            return redirect()->route('login'); // Cambia 'tu.ruta.deseada' por la ruta a la que deseas redirigir
        }

        return parent::render($request, $exception);
    }
}
