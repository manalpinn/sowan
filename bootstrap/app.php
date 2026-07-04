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

        // Trust all proxies (ngrok, reverse proxy, etc.)
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'event.admin' => \App\Http\Middleware\EventAdminMiddleware::class,
            'require.otp' => \App\Http\Middleware\RequireOtpVerification::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            $statusCodes = [401, 403, 404, 419, 429, 500, 502, 503];
            
            if (in_array($response->getStatusCode(), $statusCodes)) {
                if ($response->getStatusCode() === 419) {
                    return back()->with([
                        'message' => 'Halaman telah kadaluarsa, silakan coba lagi.',
                    ]);
                }

                // Jika sedang dalam mode debug, tampilkan stack trace bawaan Laravel untuk error 5xx
                if (in_array($response->getStatusCode(), [500, 502, 503]) && config('app.debug')) {
                    return $response;
                }

                // Jika request meminta JSON biasa (bukan Inertia), kembalikan response default
                if ($request->wantsJson() && !$request->header('X-Inertia')) {
                    return $response;
                }

                return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
