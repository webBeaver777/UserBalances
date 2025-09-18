<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Принудительно добавляем заголовки
        $request->headers->set('Accept', 'application/json');
        if (! $request->headers->has('Content-Type')) {
            $request->headers->set('Content-Type', 'application/json');
        }

        $response = $next($request);

        // На всякий случай у ответа тоже ставим JSON
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
