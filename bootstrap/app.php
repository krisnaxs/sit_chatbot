<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->append(\App\Http\Middleware\LogUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            $message = app(\App\Exceptions\DuplicateMessageParser::class)
                ->parse($e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'error' => 'duplicate_entry',
                ], 422);
            }
            return back()
                ->withInput()
                ->with('error', $message);
        });
    })
    ->create();
