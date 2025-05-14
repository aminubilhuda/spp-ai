<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Wali;
use App\Http\Middleware\Operator;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.admin' => Admin::class,
            'auth.wali' => Wali::class,
            'auth.operator' => Operator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();