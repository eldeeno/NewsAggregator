<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
        $exceptions->render(function (Throwable $e, Request $request) {
            $className = get_class($e);

            $handlers = App\Exceptions\ApiExceptionHandler::$handlers;

            if (array_key_exists($className, $handlers)) {
                $method = $handlers[$className];
                $apiHandler = new App\Exceptions\ApiExceptionHandler();
                return $apiHandler->$method($e, $request);
            }

            return response()->json([
                'error' => [
                    'type' => basename(get_class($e)),
                    'status' => $e->getCode() ?: 500,
                    'message' => $e->getMessage() ?: 'An unexpected error occurred',
                    'timestamp' => now()->toISOString(),
                    // Include debug info only in non-production environments
                    'debug' => app()->environment('local', 'testing') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ] : null
                ]
            ], $e->getCode() ?: 500);
        });
    })->create();
