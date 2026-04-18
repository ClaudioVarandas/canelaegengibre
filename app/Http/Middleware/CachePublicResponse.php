<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicResponse
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, int|string $ttl = 300): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethod('GET')
            && $response->getStatusCode() === 200
            && auth()->guest()
            && ! $request->is('admin*')) {
            $response->headers->set('Cache-Control', 'public, max-age='.(int) $ttl.', s-maxage='.(int) $ttl);
            $response->headers->set('Vary', 'Accept-Language, Cookie');
        }

        return $response;
    }
}
