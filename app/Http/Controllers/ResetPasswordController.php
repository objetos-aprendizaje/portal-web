<?php

namespace App\Http\Controllers;

use App\Models\GeneralOptionsModel;
use App\Models\ResetPasswordTokensModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends BaseController
{
    public function index(Request $request, $token)
    {
        return view('non_authenticated.reset_password', [
        ])->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function resetPassword(Request $request)
    {
        $messages = [
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener como mínimo 8 caracteres',
        ];

        $validator = Validator::make($request->all(), ['password' => 'required|min:8'], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $token = $request->input('token');
        $password = $request->input('password');

        $resetPasswordToken = ResetPasswordTokensModel::where('token', $token)->first();

        if (!$resetPasswordToken) {
            return redirect()->route('login')->with(['reset' => false, 'message' => 'El token no es válido']);
        }

        $actualDate = date("Y-m-d H:i:s");

        if ($resetPasswordToken->expiration_date < $actualDate) {
            return redirect()->route('login')->withErrors(['Ha expirado el tiempo para restablecer la contraseña. Por favor, solicítelo de nuevo']);
        }

        DB::transaction(function () use ($resetPasswordToken, $password, $actualDate) {
            $user = UsersModel::where('uid', $resetPasswordToken->uid_user)->first();
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->save();

            // Invalidamos el token añadiendo la fecha de expiracion actual
            $resetPasswordToken->expiration_date = $actualDate;
            $resetPasswordToken->save();
        });

        return redirect()->route('login')->with([
            'success' => ['Se ha restablecido la contraseña'],
        ]);
    }
}
