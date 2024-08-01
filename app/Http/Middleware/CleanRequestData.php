<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanRequestData
{
    public function handle(Request $request, Closure $next)
    {
        $data = $request->all();

        array_walk($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });

        $request->merge($data);

        return $next($request);
    }
}
