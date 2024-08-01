<?php

namespace App\Http\Controllers;

use App\Models\GeneralOptionsModel;
use App\Models\UserRolesModel;
use App\Models\UsersModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Saml2TenantsModel;


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

    private function getCasUrl() {
        $loginCas = app('general_options')['cas_active'];

        if($loginCas) {
            $loginCasUrl = Saml2TenantsModel::where('key', 'cas')->first();
            $urlCas = url('saml2/' . $loginCasUrl->uuid . '/login');
        } else $urlCas = false;

        return $urlCas;
    }

    private function getRedirisUrl() {
        $loginRediris = app('general_options')['rediris_active'];

        if($loginRediris) {
            $loginRedirisUrl = Saml2TenantsModel::where('key', 'rediris')->first();
            $urlRediris = url('saml2/' . $loginRedirisUrl->uuid . '/login');
        } else $urlRediris = false;

        return $urlRediris;
    }

    public function submit(Request $request)
    {

        DB::transaction(function () use ($request) {
            $user = new UsersModel();
            $user->uid = generate_uuid();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);
            $user->save();

            // Rol de estudiante
            $studentRol = UserRolesModel::where('code', 'STUDENT')->first();
            $user->roles()->attach($studentRol->uid, [
                'uid' => generate_uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

    }
}
