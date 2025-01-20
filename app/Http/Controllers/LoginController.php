<?php

namespace App\Http\Controllers;

use App\Models\GeneralOptionsModel;
use App\Models\Saml2TenantsModel;
use App\Models\UserRolesModel;
use App\Models\UsersAccessesModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use stdClass;
use Illuminate\Support\Facades\Cache;

class LoginController extends BaseController
{
    public function index()
    {

        $logoBd = GeneralOptionsModel::where('option_name', 'poa_logo_1')->first();

        if ($logoBd != null) {
            $logo = $logoBd['option_value'];
        }
        else {
            $logo = null;
        }

        $urlCas = $this->getCasUrl();
        $urlRediris = $this->getRedirisUrl();

        $parametersLoginSystems = Cache::get('parameters_login_systems');

        return view('non_authenticated.login', [
            "page_name" => "Inicia sesión",
            "page_title" => "Inicia sesión",
            "logo" => $logo,
            "resources" => [
                "resources/js/login.js"
            ],
            "cert_login" => env('DOMINIO_CERTIFICADO'),
            "urlCas" => $urlCas,
            "urlRediris" => $urlRediris,
            "parameters_login_systems" => $parametersLoginSystems
        ]);
    }

    private function getCasUrl()
    {
        $loginCas = app('general_options')['cas_active'];

        if ($loginCas) {
            $loginCasUrl = Saml2TenantsModel::where('key', 'cas')->first();
            $urlCas = url('saml2/' . $loginCasUrl->uuid . '/login');
        } else {
            $urlCas = false;
        }

        return $urlCas;
    }

    private function getRedirisUrl()
    {
        $loginRediris = app('general_options')['rediris_active'];

        if ($loginRediris) {
            $loginRedirisUrl = Saml2TenantsModel::where('key', 'rediris')->first();
            $urlRediris = url('saml2/' . $loginRedirisUrl->uuid . '/login');
        } else {
            $urlRediris = false;
        }
        return $urlRediris;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = UsersModel::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return redirect('/login')->with(['user_not_found' => true]);
        }

        if (!$user->verified) {
            return redirect('/login')->with(['user_not_verified' => true, 'email' => $user->email]);
        }

        Auth::login($user);
        $redirectUrl = $this->getRedirectUrlAfterLogin($request);

        return redirect($redirectUrl);
    }

    private function loginUser($user)
    {

        $userBd = UsersModel::where('email', $user->email)->with('roles')->first();

        if (!$userBd) {
            $userBd = new UsersModel();
            $userBd->uid = generate_uuid();
            $userBd->first_name = $user->first_name;
            if (isset($user->last_name)) {
                $userBd->last_name = $user->last_name;
            }
            $userBd->email = $user->email;
            $userBd->save();

            $studentRole = UserRolesModel::where('code', 'STUDENT')->first();

            $userBd->roles()->attach($studentRole->uid, ['uid' => generate_uuid()]);
        } else {
            $roleCodes = array_column($userBd->toArray()['roles'], 'code');

            if (!in_array('STUDENT', $roleCodes)) {
                abort(404);
            }
        }

        $this->saveUserAccess($userBd);
        Auth::login($userBd);
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect('/');
    }

    public function handleSocialCallback($loginMethod)
    {
        $this->validateLoginMethod($loginMethod);

        $userSocialLogin = Socialite::driver($loginMethod)->user();

        $user = new stdClass();
        if ($loginMethod == "google") {
            $user->email = $userSocialLogin->email;
            $user->first_name = $userSocialLogin->user['given_name'];
            $user->last_name = $userSocialLogin->user['family_name'] ?? null;
        } else if ($loginMethod == "facebook") {
            $user->email = $userSocialLogin->email;
            $user->first_name = $userSocialLogin->email;
        } else if ($loginMethod == "twitter") {
            $user->email = $userSocialLogin->email;
            $user->first_name = $userSocialLogin->name;
        } else if ($loginMethod == "linkedin-openid") {
            $user->email = $userSocialLogin->email;
            $user->first_name = $userSocialLogin->user['given_name'];
            $user->last_name = $userSocialLogin->user['family_name'];
        }

        try {
            $this->loginUser($user);
        } catch (\Exception $e) {
            return redirect('login')->withErrors($e->getMessage());
        }

        return redirect('/');
    }

    public function redirectToSocialLogin($loginMethod)
    {
        $this->validateLoginMethod($loginMethod);
        return Socialite::driver($loginMethod)->redirect();
    }

    private function validateLoginMethod($loginMethod)
    {
        if (!in_array($loginMethod, ['google', 'twitter', 'facebook', 'linkedin-openid'])) {
            throw new \Exception('Método de login no válido');
        }
    }

    private function getRedirectUrlAfterLogin($request)
    {
        $urlCurrent = $request->session()->get('url.current');
        if ($urlCurrent) {
            $request->session()->forget('url.current');
            return $urlCurrent;
        } else {
            return '/';
        }
    }

    private function saveUserAccess($user)
    {
        UsersAccessesModel::insert([
            'uid' => generate_uuid(),
            'user_uid' => $user->uid,
            'date' => date('Y-m-d H:i:s')
        ]);
    }
}
