<?php

use App\Http\Middleware\CaptureTracking;
use App\Http\Middleware\SecurityHeaders;
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
        $middleware->web(append: [SecurityHeaders::class, CaptureTracking::class]);
        // Tracking cookies hold only ad parameters (no personal data) and stay readable as plain JSON.
        $middleware->encryptCookies(except: ['gt_first', 'gt_last']);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        // Visitor tracking cookies are plain JSON, readable by tag scripts if needed.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
