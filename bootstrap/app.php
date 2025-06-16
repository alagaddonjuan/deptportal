<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\AuthServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add your middleware alias registration here:
        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\AdminMiddleware::class,
            // You can add other aliases here in the future if needed
            // e.g., 'isTeacher' => \App\Http\Middleware\TeacherMiddleware::class,
        ]);
    })
     ->withProviders([ // <-- Add this section to register providers
        AuthServiceProvider::class, // Register your AuthServiceProvider
        // App\Providers\EventServiceProvider::class, // Default providers are usually auto-discovered
        // App\Providers\RouteServiceProvider::class, // or listed in config/app.php
                                                    // But explicitly adding AuthServiceProvider here ensures it's loaded
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();