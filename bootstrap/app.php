<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'module'     => \App\Http\Middleware\ModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('login', 'register')) {
                $secondes = $e->getHeaders()['Retry-After'] ?? 60;

                return back()
                    ->withErrors(['email' => "Trop de tentatives. Réessayez dans {$secondes} secondes."])
                    ->onlyInput('email');
            }
        });
    })->create();
