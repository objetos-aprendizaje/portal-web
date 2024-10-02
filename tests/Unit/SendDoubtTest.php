<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\SendDoubt;
use Illuminate\Support\Facades\Mail;

class SendDoubtTest extends TestCase
{
    /**
     * Prueba para verificar la creación de una instancia del correo.
     *
     * @return void
     */
    public function testEmailInstance()
    {
        // Datos de prueba
        $name = 'Juan Pérez';
        $email = 'juan@example.com';
        $userMessage = '¿Cuál es el horario de atención?';

        // Creamos una nueva instancia de SendDoubt
        $emailInstance = new SendDoubt($name, $email, $userMessage);

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
        $name = 'Juan Pérez';
        $email = 'juan@example.com';
        $userMessage = '¿Cuál es el horario de atención?';

        // Enviamos el correo
        Mail::to('test@example.com')->send(new SendDoubt($name, $email, $userMessage));

        // Afirmamos que el correo fue enviado
        Mail::assertSent(SendDoubt::class, function ($mail) {
            return $mail->envelope()->subject === "Has recibido una pregunta";
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
        $name = 'Juan Pérez';
        $email = 'juan@example.com';
        $userMessage = '¿Cuál es el horario de atención?';

        // Creamos un nuevo objeto SendDoubt
        $emailInstance = new SendDoubt($name, $email, $userMessage);

        // Verificamos que el contenido del correo tenga la vista correcta
        $this->assertEquals('emails.doubt', $emailInstance->content()->view);
    }

    /**
     * Prueba para verificar los archivos adjuntos del correo electrónico.
     *
     * @return void
     */
    public function testEmailAttachments()
    {
        // Datos de prueba
        $name = 'Juan Pérez';
        $email = 'juan@example.com';
        $userMessage = '¿Cuál es el horario de atención?';

        // Creamos un nuevo objeto SendDoubt
        $emailInstance = new SendDoubt($name, $email, $userMessage);

        // Verificamos que no haya archivos adjuntos
        $this->assertEmpty($emailInstance->attachments());
    }
}
