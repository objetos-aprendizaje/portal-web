<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\FooterPagesModel;
use App\Models\UserPoliciesAcceptedModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptancePoliciesControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de aceptación de políticas se carga con las políticas que el usuario debe aceptar
     */
    public function testIndexLoadsAcceptancePoliciesPageWithPoliciesToAccept()
    {
         // Buscamos un usuario  
         $user = UsersModel::where('email', 'admin@admin.com')->first();
         // Si no existe el usuario lo creamos
         if (!$user) {
             $user = UsersModel::factory()->create([
                 'email'=>'admin@admin.com'
             ])->first();
         }
         // Lo autenticarlo         
         $this->actingAs($user);

        // Crear algunas políticas de footer
        $footerPage1 = FooterPagesModel::factory()->create(['acceptance_required' => 1, 'version' => 1]);
        $footerPage2 = FooterPagesModel::factory()->create(['acceptance_required' => 1, 'version' => 2]);

        // Crear una aceptación de política para la primera página (versión 1)
        UserPoliciesAcceptedModel::factory()->create([
            'user_uid' => $user->uid,
            'footer_page_uid' => $footerPage1->uid,
            'version' => 1,
        ]);

        // No hay aceptación para la segunda política
        // Se espera que el usuario deba aceptar la segunda página y la primera si hay una versión nueva

        // Simular la lógica de verificación de políticas y crear la sesión
        $policiesMustAccept = [$footerPage2];
        session(['policiesMustAccept' => $policiesMustAccept]);

        // Hacer una solicitud GET a la ruta de aceptación de políticas
        $response = $this->get(route('policiesAccept'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la vista correcta se cargue
        $response->assertViewIs('accept_policies');

        // Verificar que las políticas que el usuario debe aceptar se pasen correctamente a la vista
        $response->assertViewHas('policiesMustAccept', $policiesMustAccept);

        // Verificar que los recursos JavaScript se pasen correctamente a la vista
        $response->assertViewHas('resources', ["resources/js/accept_policies.js"]);
    }


    /**
     * @test
     * Prueba que verifica que las políticas son aceptadas y almacenadas correctamente en la base de datos.
     */
    public function testAcceptPoliciesStoresPoliciesCorrectly()
    {
        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email'=>'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        // Crear algunas políticas en la base de datos
        $policy1 = FooterPagesModel::factory()->create(['version' => 1]);
        $policy2 = FooterPagesModel::factory()->create(['version' => 1]);

        // Simular los datos de la solicitud
        $requestData = [
            'acceptedPolicies' => [
                $policy1->uid,
                $policy2->uid,
            ],
        ];

        // Hacer la solicitud POST a la ruta de aceptación de políticas
        $response = $this->post('/accept_policies/submit', $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que las políticas han sido guardadas correctamente en la base de datos
        $this->assertDatabaseHas('user_policies_accepted', [
            'user_uid' => $user->uid,
            'footer_page_uid' => $policy1->uid,
            'version' => $policy1->version,
        ]);

        $this->assertDatabaseHas('user_policies_accepted', [
            'user_uid' => $user->uid,
            'footer_page_uid' => $policy2->uid,
            'version' => $policy2->version,
        ]);

        // Verificar que la respuesta contiene el mensaje esperado
        $response->assertJson([
            'message' => 'Preferencias guardadas correctamente'
        ]);
    }
}
