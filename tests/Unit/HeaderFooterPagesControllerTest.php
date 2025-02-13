<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\FooterPagesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HeaderFooterPagesControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que se carga la página desde el header cuando el slug coincide
     */
    public function testIndexLoadsPageFromHeader()
    {
        // Crear una página en el header
        $headerPage = HeaderPagesModel::factory()->create([
            'slug' => 'test-page',
            'name' => 'Test Page name'
        ])->latest()->first();

        // Hacer una solicitud GET a la ruta con el slug de la página
        $response = $this->get('/page/test-page');

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se carga
        $response->assertViewIs('page_footer');

        // Verificar que los datos de la página se pasan correctamente a la vista
        $response->assertViewHas('page', $headerPage);
        $response->assertViewHas('name', 'Test Page name');
    }

    /**
     * @test
     * Prueba que se carga la página desde el footer cuando el slug coincide
     */
    public function testIndexLoadsPageFromFooter()
    {
        // Crear una página en el footer
        $footerPage = FooterPagesModel::factory()->create([
            'slug' => 'footer-page',
            'name' => 'Footer Page name'
        ])->latest()->first();

        // Hacer una solicitud GET a la ruta con el slug de la página
        $response = $this->get('/page/footer-page');

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se carga
        $response->assertViewIs('page_footer');

        // Verificar que los datos de la página y recursos se pasan correctamente a la vista
        $response->assertViewHas('page', $footerPage);
        $response->assertViewHas('name', 'Footer Page name');
      
    }

    /**
     * @test
     * Prueba que se devuelve un error 404 cuando la página no se encuentra en el header ni en el footer
     */
    public function testIndexReturns404IfPageNotFound()
    {
        // Hacer una solicitud GET a la ruta con un slug que no existe
        $response = $this->get('/page/non-existing-page');

        // Verificar que se devuelve un error 404
        $response->assertStatus(404);
    }
}
