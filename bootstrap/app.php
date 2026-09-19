<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $throwable): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = match ($status) {
                404 => 'Recurso no encontrado.',
                405 => 'Metodo no permitido.',
                429 => 'Demasiadas solicitudes. Intente nuevamente mas tarde.',
                default => $status >= 500
                    ? 'Error interno del servidor.'
                    : 'No se pudo procesar la solicitud.',
            };

            return response()->json(['message' => $message], $status);
        });
    })->create();
