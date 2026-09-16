<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\EnsureAdminAreaIsSecure;
use App\Http\Middleware\RedirectLegacySlug;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('web', RedirectLegacySlug::class);
        $middleware->appendToGroup('web', EnsureAdminAreaIsSecure::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\HandleInertiaRequests::class);

        $middleware->validateCsrfTokens(except: [
            'harmony-access/media',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
