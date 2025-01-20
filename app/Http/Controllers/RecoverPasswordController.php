<?php
namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Models\ResetPasswordTokensModel;
use App\Models\UsersModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RecoverPasswordController extends BaseController
{
    protected $mailService;

    public function index()
    {
        return view('non_authenticated.recover_password', [
            "page_name" => "Restablecer contraseña",
            "page_title" => "Restablecer contraseña",
            "resources" => [
                "resources/js/recover_password.js",
            ]
        ]);
    }

    public function recoverPassword(Request $request)
    {
        $email = $request->input('email');

        $user = UsersModel::where('email', $email)->first();

        if ($user) {
            $this->sendEmailRecoverPassword($user);
        }

        return redirect()->route('login')->with([
            'sent_email_recover_password' => true,
            'email' => $user->email ?? ""
        ]);
    }

    private function sendEmailRecoverPassword($user)
    {
        $token = md5(uniqid(rand(), true));
        $minutesExpirationToken = env('PWRES_TOKEN_EXPIRATION_MIN', 60);
        $expirationDate = date("Y-m-d H:i:s", strtotime("+$minutesExpirationToken minutes"));

        // Insertar el token en la tabla password_reset_tokens
        $resetPasswordToken = new ResetPasswordTokensModel();
        $resetPasswordToken->uid = generate_uuid();
        $resetPasswordToken->uid_user = $user->uid;
        $resetPasswordToken->email = $user->email;
        $resetPasswordToken->token = $token;
        $resetPasswordToken->expiration_date = $expirationDate;
        $resetPasswordToken->save();

        $url = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(config('auth.passwords.users.expire')),
            ['token' => $token, 'email' => $user->email]
        );

        $parameters = [
            'url' => $url
        ];

        dispatch(new SendEmailJob($user->email, 'Restablecer contraseña', $parameters, 'emails.reset_password_new'));
        Log::info('Email enviado para reestablecer contraseña ' . $url);
    }
}
