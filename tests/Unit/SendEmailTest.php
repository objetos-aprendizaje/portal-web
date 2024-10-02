<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;

class SendEmailTest extends TestCase
{

    /**
     * Prueba para verificar el envío de un correo electrónico.
     *
     * @return void
     */
    public function testSendingEmail()
    {
        // Falsificamos el envío de correos
        Mail::fake();

        // Creamos una instancia del correo con los parámetros necesarios
        $subject = 'Asunto esperado';
        $parameters = []; // Aquí puedes añadir los parámetros que necesites
        $template = 'nombre.del.template'; // Reemplaza con el nombre del template real

        // Enviamos el correo
        Mail::to('test@example.com')->send(new SendEmail($subject, $parameters, $template));

        // Afirmamos que el correo fue enviado
        Mail::assertSent(SendEmail::class, function ($mail) use ($subject) {
            // Verificamos que el correo tenga el asunto correcto
            return $mail->envelope()->subject === $subject;
        });
    }

    /**
     * Prueba para verificar el contenido del correo electrónico.
     *
     * @return void
     */
    public function testEmailContent()
    {
        // Creamos una instancia del correo con los parámetros necesarios
        $subject = 'Asunto esperado';
        $parameters = []; // Aquí puedes añadir los parámetros que necesites
        $template = 'nombre.del.template'; // Reemplaza con el nombre del template real

        // Creamos un nuevo objeto SendEmail
        $email = new SendEmail($subject, $parameters, $template);

        // Verificamos que el contenido del correo tenga la vista correcta
        $this->assertEquals($template, $email->content()->view);
    }

    /**
     * Prueba para verificar los archivos adjuntos del correo electrónico.
     *
     * @return void
     */
    public function testEmailAttachments()
    {
        // Creamos una instancia del correo con los parámetros necesarios
        $subject = 'Asunto esperado';
        $parameters = []; // Aquí puedes añadir los parámetros que necesites
        $template = 'nombre.del.template'; // Reemplaza con el nombre del template real

        // Creamos un nuevo objeto SendEmail
        $email = new SendEmail($subject, $parameters, $template);

        // Verificamos que no haya archivos adjuntos
        $this->assertEmpty($email->attachments());
    }
}
