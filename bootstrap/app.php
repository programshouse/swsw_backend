<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',

         api: [
            __DIR__ . '/../routes/api_delivery.php',
             __DIR__ . '/../routes/api_user.php',
            // __DIR__ . '/../routes/api_admin.php',
            //  __DIR__ . '/../routes/api_landing.php',
        ],

     
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    // ->withBroadcasting(
    //     channels: __DIR__ . '/../routes/channels.php',
    //     attributes: ['prefix' => 'api', 'middleware' => ['api']],
    // )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
        'admin' => Admin::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
