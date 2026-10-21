<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Webhook Midtrans dikirim server-ke-server, tidak membawa token CSRF.
        $middleware->validateCsrfTokens(except: [
            'midtrans/notifikasi',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
