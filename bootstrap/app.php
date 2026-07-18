<?php

use App\Http\Middleware\Admin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',

        api: [
            __DIR__ . '/../routes/api_delivery.php',
            __DIR__ . '/../routes/api_user.php',
        ],

        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => Admin::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect unauthenticated users
        |--------------------------------------------------------------------------
        |
        | أي مستخدم يفتح Route محمي بدون تسجيل دخول
        | سيتم تحويله إلى صفحة اختيار نوع الحساب:
        |
        | /login
        |
        | ومن خلالها يختار أدمن أو سيلز.
        |
        */

        $middleware->redirectGuestsTo(
            fn () => route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();