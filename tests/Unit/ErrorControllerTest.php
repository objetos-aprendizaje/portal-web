<?php

namespace Tests\Unit;

use Tests\TestCase;

class ErrorControllerTest extends TestCase
{
    /**
     * @test
     * Prueba que verifica que la página de error muestra el mensaje correcto según el código de error proporcionado.
     */
    public function testErrorPageDisplaysCorrectMessage()
    {
        // Caso 1: Código de error 001 (Pago)
        $response = $this->get(route('error', ['code' => '001']));
        $response->assertStatus(200);
        $response->assertViewIs('error');
        $response->assertViewHas('errorMessage', 'Ha ocurrido un error con el pago');

        // Caso 2: Código de error 002 (Enlace de verificación incorrecto)
        $response = $this->get(route('error', ['code' => '002']));
        $response->assertStatus(200);
        $response->assertViewIs('error');
        $response->assertViewHas('errorMessage', 'El enlace de verificación es incorrecto');

        // Caso 3: Código de error desconocido (Por defecto)
        $response = $this->get(route('error', ['code' => '999']));
        $response->assertStatus(200);
        $response->assertViewIs('error');
        $response->assertViewHas('errorMessage', 'Ha ocurrido un error');
    }
}
