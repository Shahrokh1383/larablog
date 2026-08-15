<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Shared\Exceptions\DomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->is('broadcasting/*') || $request->expectsJson();
        });

        // Redirect OAuth callback domain exceptions to the SPA with a generic error flag
        $exceptions->renderable(function (DomainException $e, Request $request) {
            if ($request->is(['oauth/*/callback', 'api/oauth/*/callback'])) {
                $frontendUrl = config('app.frontend_url');
                return redirect()->to("{$frontendUrl}/oauth-callback?error=oauth_failed");
            }
        });

        // Handle validation errors from OAuth callback without leaking JSON/HTML
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is(['oauth/*/callback', 'api/oauth/*/callback'])) {
                $frontendUrl = config('app.frontend_url');
                $message = collect($e->errors())->flatten()->implode(' ');
                $message = $message ?: 'OAuth validation failed.';

                return redirect()->to("{$frontendUrl}/oauth-callback?error=" . urlencode($message));
            }
        });
    })->create();