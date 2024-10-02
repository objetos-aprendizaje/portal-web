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

        $logo_bd = GeneralOptionsModel::where('option_name', 'poa_logo_1')->first();

        if ($logo_bd != null) $logo = $logo_bd['option_value'];
        else $logo = null;

        $urlCas = $this->getCasUrl();
        $urlRediris = $this->getRedirisUrl();

        $parameters_login_systems = Cache::get('parameters_login_systems');

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
            "parameters_login_systems" => $parameters_login_systems
        ]);
    }

    private function getCasUrl()
    {
        $loginCas = app('general_options')['cas_active'];

        if ($loginCas) {
            $loginCasUrl = Saml2TenantsModel::where('key', 'cas')->first();
            $urlCas = url('saml2/' . $loginCasUrl->uuid . '/login');
        } else $urlCas = false;

        return $urlCas;
    }

    private function getRedirisUrl()
    {
        $loginRediris = app('general_options')['rediris_active'];

        if ($loginRediris) {
            $loginRedirisUrl = Saml2TenantsModel::where('key', 'rediris')->first();
            $urlRediris = url('saml2/' . $loginRedirisUrl->uuid . '/login');
        } else $urlRediris = false;

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

        $user_bd = UsersModel::where('email', $user->email)->with('roles')->first();

        if (!$user_bd) {
            $user_bd = new UsersModel();
            $user_bd->uid = generate_uuid();
            $user_bd->first_name = $user->first_name;
            if (isset($user->last_name)) $user_bd->last_name = $user->last_name;
            $user_bd->email = $user->email;
            $user_bd->save();

            $student_role = UserRolesModel::where('code', 'STUDENT')->first();

            $user_bd->roles()->attach($student_role->uid, ['uid' => generate_uuid()]);
        } else {
            $role_codes = array_column($user_bd->toArray()['roles'], 'code');

            if (!in_array('STUDENT', $role_codes)) abort(404);
        }

        $this->saveUserAccess($user_bd);
        Auth::login($user_bd);
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
            $user->last_name = $userSocialLogin->user['family_name'];
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

    public function tokenLogin($token)
    {

        $user = UsersModel::where('token_x509', $token)->first();

        if ($user) {
            Auth::login($user);
            $this->deleteTokenLogin($user);
            return redirect("https://" . env('DOMINIO_PRINCIPAL'));
        } else {
            // $this->deleteTokenLogin($user); // Se comento esta linea ya que nunca obtendremos el valor de $user
            return redirect("https://" . env('DOMINIO_PRINCIPAL') . "/login?e=certificate-error");
        }
    }

    private function deleteTokenLogin($user)
    {
        $user->token_x509 = "";
        $user->save();
    }

    private function getRedirectUrlAfterLogin($request)
    {
        $urlCurrent = $request->session()->get('url.current');
        if ($urlCurrent) {
            $request->session()->forget('url.current');
            return $urlCurrent;
        } else return '/';
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
