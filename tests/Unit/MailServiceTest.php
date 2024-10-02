<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Services\MailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailServiceTest extends TestCase
{

    // use RefreshDatabase;

    /**
     * @test
     * Prueba que el correo de restablecimiento de contraseña se envía correctamente.
     */
    // public function testSendResetPasswordMailSendsEmail()
    // {
    //     // Crear un usuario simulado
    //     $user = UsersModel::factory()->create([
    //         'email' => 'test@example.com',
    //         'first_name' => 'Test',
    //         'last_name' => 'User',
    //     ]);

    //     $user = UsersModel::where('email', 'test@example.com')->first();

    //     // Simular los datos que se enviarán al correo
    //     $data = [
    //         'token' => 'test-token',
    //         'url' => 'https://example.com/reset-password?token=test-token'
    //     ];

    //     // Fake del envío de correo
    //     Mail::fake();

    //     // Instanciar el servicio de correo y llamar al método
    //     $mailService = new MailService();
    //     $mailService->sendResetPasswordMail($user, $data);

    //     // Verificar que se haya enviado un correo sin verificar los detalles específicos
    //     Mail::assertSent(function ($mail) use ($user) {
    //         // dd($mail);
    //         return $mail->hasTo($user->email);
    //     });

    // }
}
