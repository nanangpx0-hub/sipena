<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckDeadline;
use App\Http\Middleware\EnsureUserHasRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias otorisasi RBAC: role:approver,editor
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Penjaga Soft/Hard Deadline aktif pada seluruh request web.
        $middleware->web(append: [
            CheckDeadline::class,
        ]);

        // Redirect batal masuk ke halaman login, bukan "/login" generik.
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
