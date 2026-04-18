<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, ?string $locale = null): Response
    {
        $resolved = $locale ?? config('canela.default_locale');

        if (! array_key_exists($resolved, config('canela.locales', []))) {
            $resolved = config('canela.default_locale');
        }

        app()->setLocale($resolved);

        return $next($request);
    }
}
