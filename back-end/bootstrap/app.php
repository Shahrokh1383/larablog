<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Identity\Exceptions\OAuthCallbackFailedException;
use Modules\Identity\Exceptions\UnsupportedOAuthProviderException;

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

        // Redirect OAuth errors to the SPA with error flag
        $exceptions->renderable(function (OAuthCallbackFailedException $e, Request $request) {
            if ($request->is('oauth/*/callback')) {
                $frontendUrl = config('app.frontend_url');
                return redirect()->to("{$frontendUrl}/oauth-callback?error=oauth_failed");
            }
        });

        $exceptions->renderable(function (UnsupportedOAuthProviderException $e, Request $request) {
            if ($request->is('oauth/*/callback') || $request->is('oauth/*/redirect')) {
                $frontendUrl = config('app.frontend_url');
                return redirect()->to("{$frontendUrl}/oauth-callback?error=oauth_failed");
            }
        });
    })->create();