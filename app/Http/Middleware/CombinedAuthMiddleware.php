<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class CombinedAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        // url a la que intenta acceder
        $urlCurrent = URL::current();

        if(!str_contains($urlCurrent, 'login')) {
            $request->session()->put('url.current', $urlCurrent);
        }

        // Comprobamos si tenemos usuario
        if (Auth::check()) {
            try {
                $this->loadUserData(Auth::user());
            } catch (\Exception $e) {
                return redirect('login')->withErrors($e->getMessage());
            }

            return $next($request);
        }

        return redirect('login');
    }


    private function loadUserData($user)
    {
        $user = $user->with("roles")->first();

        if (!$user) {
            throw new \Exception('No hay ninguna cuenta asociada al email');
        }

        View::share('roles', $user->roles->toArray());
    }

}
