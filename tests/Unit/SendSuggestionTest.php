<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\SendSuggestion;
use Illuminate\Support\Facades\Mail;

class SendSuggestionTest extends TestCase
{
    /**
     * Prueba para verificar la creación de una instancia del correo.
     *
     * @return void
     */
    public function testEmailInstance()
    {
        // Datos de prueba
        $name = 'Ana Gómez';
        $email = 'ana@example.com';
        $userMessage = 'Me gustaría sugerir una nueva funcionalidad.';

        // Creamos una nueva instancia de SendSuggestion
        $emailInstance = new SendSuggestion($name, $email, $userMessage);

        // Verificamos que las propiedades estén correctamente asignadas
        $this->assertEquals($name, $emailInstance->name);
        $this->assertEquals($email, $emailInstance->email);
        $this->assertEquals($userMessage, $emailInstance->userMessage);
    }

    /**
     * Prueba para verificar el envío del correo electrónico.
     *
     * @return void
     */
    public function testSendingEmail()
    {
        // Falsificamos el envío de correos
        Mail::fake();

        // Datos de prueba
        $name = 'Ana Gómez';
        $email = 'ana@example.com';
        $userMessage = 'Me gustaría sugerir una nueva funcionalidad.';

        // Enviamos el correo
        Mail::to('test@example.com')->send(new SendSuggestion($name, $email, $userMessage));

        // Afirmamos que el correo fue enviado
        Mail::assertSent(SendSuggestion::class, function ($mail) {
            return $mail->envelope()->subject === "Nueva sugerencia";
        });
    }

    /**
     * Prueba para verificar el contenido del correo electrónico.
     *
     * @return void
     */
    public function testEmailContent()
    {
        // Datos de prueba
        $name = 'Ana Gómez';
        $email = 'ana@example.com';
        $userMessage = 'Me gustaría sugerir una nueva funcionalidad.';

        // Creamos un nuevo objeto SendSuggestion
        $emailInstance = new SendSuggestion($name, $email, $userMessage);

        // Verificamos que el contenido del correo tenga la vista correcta
        $this->assertEquals('emails.suggestion', $emailInstance->content()->view);
    }

    /**
     * Prueba para verificar los archivos adjuntos del correo electrónico.
     *
     * @return void
     */
    public function testEmailAttachments()
    {
        // Datos de prueba
        $name = 'Ana Gómez';
        $email = 'ana@example.com';
        $userMessage = 'Me gustaría sugerir una nueva funcionalidad.';

        // Creamos un nuevo objeto SendSuggestion
        $emailInstance = new SendSuggestion($name, $email, $userMessage);

        // Verificamos que no haya archivos adjuntos
        $this->assertEmpty($emailInstance->attachments());
    }
}
