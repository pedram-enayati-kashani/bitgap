<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenExists
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $publicRoutes = [
            'api/register',
            'api/login',
        ];

        $path = ltrim($request->path(), '/');

        if (!in_array($path, $publicRoutes) && $request->is('api/*')) {
            $header = $request->header('Authorization');
            if (!$header || !str_starts_with($header, 'Bearer ')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        }

        return $next($request);
    }
}