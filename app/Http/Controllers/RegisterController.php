<?php

namespace App\Http\Controllers;

use App\Exceptions\OperationFailedException;
use App\Models\GeneralOptionsModel;
use App\Models\UserRolesModel;
use App\Models\UsersModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Saml2TenantsModel;
use Illuminate\Support\Facades\URL;
use App\Jobs\SendEmailJob;
use App\Models\EmailVerifyModel;


class RegisterController extends BaseController
{
    public function index()
    {
        $logo_bd = GeneralOptionsModel::where('option_name', 'poa_logo_1')->first();

        if ($logo_bd != null) $logo = $logo_bd['option_value'];
        else $logo = null;

        $urlCas = $this->getCasUrl();
        $urlRediris = $this->getRedirisUrl();

        $parameters_login_systems = Cache::get('parameters_login_systems');

        return view('non_authenticated.register', [
            "page_name" => "Regístrate",
            "page_title" => "Regístrate",
            "logo" => $logo,
            "resources" => [
                "resources/js/register.js"
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

    private function validateUser($request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password'
        ], [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.string' => 'El nombre debe ser una cadena de texto.',
            'first_name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.string' => 'El apellido debe ser una cadena de texto.',
            'last_name.max' => 'El apellido no puede tener más de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
            'password_confirmation.same' => 'La confirmación de la contraseña no coincide.'
        ]);
    }

    public function submit(Request $request)
    {
        $this->validateUser($request);

        $user = new UsersModel();
        $user->uid = generate_uuid();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = password_hash($request->password, PASSWORD_BCRYPT);

        DB::transaction(function () use ($user) {
            $user->save();

            // Rol de estudiante
            $studentRol = UserRolesModel::where('code', 'STUDENT')->first();
            $user->roles()->attach($studentRol->uid, [
                'uid' => generate_uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            sendEmail($user);
        });

        return redirect("/login")->with([
            'account_created' => $user->email,
            'email' => $user->email,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $verify = EmailVerifyModel::where('token', $request->token)->first();

        if (!$verify) return redirect('/error/002');

        $user_bd = UsersModel::where('uid', $verify->user_uid)->with('roles')->first();

        if ($verify->expires_at <=  now()) {
            return redirect('/login')->with([
                'verify_link_expired' => true,
                'email' => $user_bd->email
            ]);
        }

        $user_bd->verified = true;

        DB::transaction(function () use ($verify, $user_bd, $request) {
            $user_bd->save();
            EmailVerifyModel::where('token', $request->token)->delete();
        });

        return redirect('/login')->with([
            'email_verified' => $user_bd->email
        ]);
    }

    public function resendEmailConfirmation(Request $request)
    {
        $email = $request->email;

        $user = UsersModel::where('email', $email)->first();

        if (!$user) {
            throw new OperationFailedException('No se ha encontrado ninguna cuenta con esa dirección de correo', 404);
        }

        DB::transaction(function () use ($user) {
            // Inhabilitamos todos los tokens anteriores
            EmailVerifyModel::where('user_uid', $user->uid)->delete();
            $this->sendEmail($user);
        });

        return response()->json(['message' => 'Se ha reenviado el email']);
    }
}
