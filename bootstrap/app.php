<?php

use App\Http\Middleware\NoCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
            'nocache' => NoCache::class,
        ]);
        // Intercept unauthenticated users and redirect them to your custom login
        $middleware->redirectGuestsTo(function (Request $request) {
            // If they are trying to access a client URL, send them to client login
            if ($request->is('client') || $request->is('client/*')) {
                return route('client.login');
            }


            // Fallback for your main app (if you have a default login later)
            // return route('login'); 
    
            // If you only have the client portal right now, just do this:
            return route('client.login');
        });

        $middleware->redirectGuestsTo(function (Request $request) {
            // If they are trying to access a client URL, send them to client login
            if ($request->is('employee') || $request->is('employee/*')) {
                return route('employee.login');
            }

            // Fallback for your main app (if you have a default login later)
            // return route('login'); 
    
            // If you only have the client portal right now, just do this:
            return route('employee.login');
        });


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Intercept the Authentication Exception to inject our custom message
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {

            // Check if the user was trying to access a protected client route
            if ($request->is('client') || $request->is('client/*')) {

                // Use ->guest() to remember where they were trying to go, 
                // and ->withErrors() to trigger the red alert box on your login blade.
                return redirect()->guest(route('client.login'))
                    ->withErrors(['error' => 'Login to access the pages.']);
            }

        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {

            // Check if the user was trying to access a protected employee route
            if ($request->is('employee') || $request->is('employee/*')) {

                // Use ->guest() to remember where they were trying to go, 
                // and ->withErrors() to trigger the red alert box on your login blade.
                return redirect()->guest(route('employee.login'))
                    ->withErrors(['error' => 'Login to access the pages.']);
            }

        });
    })->create();
