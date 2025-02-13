<?php

namespace App\Http\Controllers;

use App\Models\UsersModel;
use Illuminate\Http\Request;
use App\Models\UserRolesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRoleRelationshipsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CertificateAccessController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function loginCertificate(Request $request)
    {
        $data = $request->input('data');
        $expiration = $request->input('expiration');
        $hash = $request->input('hash');

        $hashCheck = ($data . $expiration . env('KEY_CHECK_CERTIFICATE_ACCESS'));

        if ($expiration < time() || $hash != $hashCheck) {
            return redirect('/login');
        }

        $decodedHtml = html_entity_decode($data);
        $data = json_decode($decodedHtml);
        $user = UsersModel::whereRaw('UPPER(nif) = ?', [strtoupper($data->nif)])->first();

        if (!$user) {
            $user = $this->createUser($data);
        }

        if (!$user->verified) {
            session(['dataCertificate' => $user]);
            return redirect()->route('get-email');
        }

        return $this->authUser($user->email);
    }

    private function authUser($email)
    {
        $user = UsersModel::where('email', strtolower($email))->first();

        if ($user) {
            Auth::login($user);
            return redirect()->to('/');
        }
    }

    private function createUser($data)
    {
        $user = new UsersModel();
        $user->uid = generate_uuid();
        $user->email = "";
        $user->first_name = $data->first_name;
        $user->last_name = $data->last_name;
        $user->nif = strtoupper($data->nif);
        $user->identity_verified = true;

        // Asignar el rol de estudiante
        $studentRol = UserRolesModel::where("code", "STUDENT")->first();
        DB::transaction(function () use ($user, $studentRol) {
            $user->save();

            UserRoleRelationshipsModel::create([
                'uid' => generate_uuid(),
                'user_uid' => $user->uid,
                'user_role_uid' => $studentRol->uid
            ]);
        });

        return $user;
    }
}
