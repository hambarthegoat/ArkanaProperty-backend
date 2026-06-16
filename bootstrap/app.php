<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $e instanceof \Illuminate\Auth\AuthenticationException,
                $e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException => 401,
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                $e instanceof \Illuminate\Validation\ValidationException => 422,
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException,
                $e instanceof \Symfony\Component\Routing\Exception\ResourceNotFoundException => 404,
                method_exists($e, 'getStatusCode') => $e->getStatusCode(),
                default => 500,
            };

            $message = $e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException
                ? 'Unauthenticated.'
                : ($e->getMessage() ?: 'Server error');

            return response()->json([
                'success' => false,
                'data' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
                'message' => $message,
            ], $status);
        });
    })->create();
