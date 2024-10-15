<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Profile\MyProfileController;

class MyProfileControllerTest extends TestCase
{

    use RefreshDatabase;
    /**
     * @test Index My Profile
     */
    public function testIndexMyProfile()
    {
        // Arrange
        $user = UsersModel::factory()->create()->first();
        $this->actingAs($user);

        // Simula las notificaciones generales
        $generalNotifications = [
            [
                'uid' => generate_uuid(), // Asegúrate de que esto genere un UUID válido
                'type' => 'general_notification',
                'is_read' => false,
                'title' => 'Notificación de prueba',
                'description' => 'Esta es una descripción de prueba.',
                'date' => now(), // O cualquier fecha válida
            ],
            [
                'uid' => generate_uuid(),
                'type' => 'general_notification',
                'is_read' => true,
                'title' => 'Notificación leída',
                'description' => 'Descripción de una notificación leída.',
                'date' => now()->subDays(1), // Fecha anterior para simular una notificación leída
            ],
        ];

        // Establece las variables compartidas manualmente
        View::share('general_notifications', $generalNotifications);
        View::share('unread_general_notifications', true); // Cambia a false si no hay notificaciones no leídas


        // Act
        $response = $this->get('/profile/update_account');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('profile.my_profile.index');
        $response->assertSee('Mi perfil'); // Adjust as needed based on expected view content
        $response->assertSee($user->name); // Adjust as needed based on expected user data
        $response->assertSee('resources/js/my_profile.js');
    }

    /**
     * @test
     * Prueba la actualización del perfil de usuario incluyendo la subida de la imagen.
     */
    public function testUpdateUserUpdatesProfileAndUploadsImage()
    {
        // Crear un usuario de prueba y autenticarlo
        $user = UsersModel::factory()->create(
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]
        );  

        $this->actingAs($user);

        // Simular la subida de un archivo
        Storage::fake('public');
        $image = UploadedFile::fake()->image('profile.jpg');

        // Mockear la función updateImageBackend para que devuelva una ruta simulada
        // $this->partialMock(MyProfileController::class, function ($mock) {
        //     $mock->shouldReceive('updateImageBackend')
        //         ->andReturn('images/profile.jpg');
        // });

        // Simular la solicitud con datos y una imagen
        $response = $this->post('/profile/update_account/update', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'photo_path' => $image,
        ]);

        // Verificar que la respuesta es correcta (200 OK)
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Tu perfil se ha actualizado correctamente']);

        
    }
}
