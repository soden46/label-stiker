<?php

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserPortal;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn ($request) => $request->is('pos', 'pos/*') ? '/pos/login' : '/login');
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->portal === 'pos' ? '/pos' : '/dashboard');
        $middleware->alias([
            'portal' => EnsureUserPortal::class,
            'super_admin' => EnsureSuperAdmin::class,
            'permission' => EnsurePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
