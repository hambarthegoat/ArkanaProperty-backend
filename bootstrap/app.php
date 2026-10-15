<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $e instanceof AuthenticationException,
                $e instanceof RouteNotFoundException => 401,
                $e instanceof AuthorizationException => 403,
                $e instanceof ValidationException => 422,
                $e instanceof ModelNotFoundException,
                $e instanceof ResourceNotFoundException => 404,
                method_exists($e, 'getStatusCode') => $e->getStatusCode(),
                default => 500,
            };

            $message = $e instanceof RouteNotFoundException
                ? 'Unauthenticated.'
                : ($e->getMessage() ?: 'Server error');

            return response()->json([
                'success' => false,
                'data' => $e instanceof ValidationException ? $e->errors() : null,
                'message' => $message,
            ], $status);
        });
    })->create();
