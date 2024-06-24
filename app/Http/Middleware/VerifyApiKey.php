<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('API-KEY');

        // Reemplaza 'your-api-key' con la API key que quieres verificar
        if ($apiKey !== env('API_KEY_WEBHOOKS')) {
            return response()->json(['message' => 'Invalid API key'], 403);
        }

        return $next($request);
    }
}
