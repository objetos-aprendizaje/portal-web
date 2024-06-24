<?php

namespace App\Http\Middleware;

use App\Models\UsersModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class CombinedAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if (!str_contains(URL::previous(), 'login')) {
            $request->session()->put('url.intended', URL::previous());
        }

        $request->session()->regenerate();
        // Comprobamos si tenemos usuario
        if (Auth::check()) {
            try {
                $this->loadUserData(Auth::user()->email);
            } catch (\Exception $e) {
                return redirect('login')->withErrors($e->getMessage());
            }

            return $next($request);
        } elseif ($this->isAuthenticatedWithGoogle($request) || $this->isAuthenticatedWithTwitter($request) || $this->isAuthenticatedWithFacebook($request) || $this->isAuthenticatedWithLinkedin($request)) {
            try {
                $email_user = $request->session()->get('email');
                $this->loadUserData($email_user);
                return $next($request);
            } catch (\Exception $e) {
                return redirect('login')->withErrors($e->getMessage());
            }
        }

        // Redirigir a la página de inicio de sesión o mostrar un mensaje de error
        return redirect('login');
    }

    protected function isAuthenticatedWithGoogle($request)
    {
        return $request->session()->has('google_id');
    }

    protected function isAuthenticatedWithTwitter($request)
    {
        return $request->session()->has('twitter_id');
    }

    protected function isAuthenticatedWithLinkedin($request)
    {
        return $request->session()->has('linkedin_id');
    }

    protected function isAuthenticatedWithFacebook($request)
    {
        return $request->session()->has('facebook_id');
    }

    private function loadUserData($user_email)
    {
        $user = UsersModel::where('email', $user_email)->with("roles")->first();

        if (!$user) {
            throw new \Exception('No hay ninguna cuenta asociada al email');
        }

        View::share('roles', $user['roles']->toArray());
        Auth::login($user);
    }

}
