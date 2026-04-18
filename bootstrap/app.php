<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use App\Models\Redirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'set-locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->isMethod('GET')) {
                $path = '/'.ltrim($request->path(), '/');
                $redirect = Redirect::query()->where('from_path', $path)->first();
                if ($redirect !== null) {
                    return redirect($redirect->to_path, $redirect->code);
                }
            }

            return null;
        });
    })->create();
