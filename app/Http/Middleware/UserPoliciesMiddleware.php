<?php

namespace App\Http\Middleware;

use App\Models\FooterPagesModel;
use App\Models\UserPoliciesAcceptedModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;


/**
 * Si el usuario está logueado, se comprueba si tiene que aceptar alguna política de privacidad.
 */
class UserPoliciesMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Páginas de footer que están marcadas como aceptación requerida
            $footerPages = FooterPagesModel::where("acceptance_required", 1)->get();

            // Aceptaciones de políticas del usuario
            $userPoliciesAccepted = UserPoliciesAcceptedModel::where("user_uid", Auth::user()->uid)->get()->keyBy('footer_page_uid');

            $policiesMustAccept = [];

            foreach ($footerPages as $page) {
                if (!isset($userPoliciesAccepted[$page->uid]) || $userPoliciesAccepted[$page->uid]->version < $page->version) {
                    $policiesMustAccept[] = $page;
                }
            }

            if (count($policiesMustAccept)) {
                session(['policiesMustAccept' => $policiesMustAccept]);
                return Redirect::route('policiesAccept');
            }
        }

        return $next($request);
    }
}
