<?php

namespace App\Http\Controllers;

use App\Models\UsersModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class CertificateAccessController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function loginCertificate()
    {
        $data = $_GET['data'];
        $expiration = $_GET['expiration'];
        $hash = $_GET['hash'];

        $hashCheck = md5($data . $expiration . env('KEY_CHECK_CERTIFICATE_ACCESS'));

        if ($expiration < time() || $hash != $hashCheck) return redirect('/login');

        $data = json_decode($data);

        $user = UsersModel::whereRaw('UPPER(nif) = ?', [strtoupper($data->nif)])->first();

        if (!$user) $user = $this->createUser($data);

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

    private function createUser($data) {
        $user = new UsersModel();
        $user->uid = generate_uuid();
        $user->email = "";
        $user->first_name = $data->first_name;
        $user->last_name = $data->last_name;
        $user->nif = strtoupper($data->nif);
        $user->save();

        return $user;
    }
}
