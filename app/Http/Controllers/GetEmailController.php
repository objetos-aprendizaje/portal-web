<?php

namespace App\Http\Controllers;

use App\Models\ResetPasswordTokensModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\UserRolesModel;
use App\Models\UserRoleRelationshipsModel;

class GetEmailController extends BaseController
{
    public function index(Request $request)
    {
        return view(
            'non_authenticated.get_email'
        );
    }
    public function addUser(Request $request)
    {

        $userSession = session('dataCertificate');

        $this->validateEmail($request);

        session()->forget('dataCertificate');

        $user = UsersModel::whereRaw('LOWER(nif) = ?', [strtolower($userSession['nif'])])->first();
        $user->email = $request->email;
        $user->identity_verified = true;
        $user->save();

        sendEmail($user);

        return redirect("/login")->with([
            'account_created' => $user->email,
            'email' => $user->email,
        ]);
    }

    private function validateEmail($request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'email_verification' => 'required|same:email'
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'email_verification.required' => 'La confirmación del correo es obligatoria.',
            'email_verification.same' => 'La confirmación del correo no coincide.'
        ]);
    }

}
