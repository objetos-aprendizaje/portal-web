<?php

namespace App\Http\Controllers;

use App\Models\GeneralOptionsModel;
use App\Models\Saml2TenantsModel;
use App\Models\UserRolesModel;
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

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user_google = Socialite::driver('google')->user();

        $user = new stdClass();
        $user->email = $user_google->email;
        $user->first_name = $user_google->user['given_name'];
        $user->last_name = $user_google->user['family_name'];

        $this->loginUser($user);

        session(['email' => $user_google->email, 'google_id' => $user_google->id, 'token_google' => $user_google->token]);

        return redirect('/');
    }

    public function redirectToTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleTwitterCallback()
    {
        $user_twitter = Socialite::driver('twitter')->user();

        $user_twitter->email = 'ja.cabello11@asesoresnt.com';

        $user = new stdClass();
        $user->email = $user_twitter->email;
        $user->first_name = $user_twitter->name;

        $this->loginUser($user);

        session(['email' => $user_twitter->email, 'twitter_id' => $user_twitter->id, 'token_twitter' => $user_twitter->token]);

        return redirect('/');
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $user_facebook = Socialite::driver('facebook')->user();

        session(['email' => $user_facebook->email, 'facebook_id' => $user_facebook->id, 'token_facebook' => $user_facebook->token]);

        return redirect('/#');
    }

    public function redirectToLinkedin()
    {
        return Socialite::driver('linkedin-openid')->redirect();
    }

    public function handleLinkedinCallback()
    {
        $user_linkedin = Socialite::driver('linkedin-openid')->user();

        $user = new stdClass();
        $user->email = $user_linkedin->email;
        $user->name = $user_linkedin->name;

        $this->loginUser($user);

        session(['email' => $user_linkedin->email, 'linkedin_id' => $user_linkedin->id, 'token_linkedin' => $user_linkedin->token]);

        return redirect('/');
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

        Auth::login($user_bd);
    }

    public function logout()
    {
        if (Session::get('google_id')) {
            $token = Session::get('token_google');

            $client = new \GuzzleHttp\Client();

            try {
                $client->post('https://oauth2.googleapis.com/revoke', [
                    'form_params' => ['token' => $token]
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
            }
        }

        Session::flush();
        Auth::logout();

        return redirect('/');
    }

    public function tokenLogin($token)
    {

        $user = UsersModel::where('token_x509', $token)->first();

        if ($user) {
            Auth::login($user);
            $this->deleteTokenLogin($user);
            return redirect("https://" . env('DOMINIO_PRINCIPAL'));
        } else {
            $this->deleteTokenLogin($user);
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
}
