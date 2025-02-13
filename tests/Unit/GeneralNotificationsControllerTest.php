<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\GeneralNotificationsModel;
use App\Models\GeneralNotificationsAutomaticModel;
use Illuminate\Foundation\Testing\RefreshDatabase;


class GeneralNotificationsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Prueba que devuelve correctamente la notificación general del usuario y la marca como leída
     */
    public function testGetGeneralNotificationUserMarksAsRead()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Crear una notificación general en la base de datos
        $generalNotification = GeneralNotificationsModel::factory()->create([
            'uid' => generate_uuid(),
        ])->first();

        // Hacer una solicitud GET a la ruta de la notificación general del usuario
        $response = $this->get('/notifications/general/get_general_notification_user/' . $generalNotification->uid);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la notificación se ha marcado como leída
        $this->assertDatabaseHas('user_general_notifications', [
            'user_uid' => $user->uid,
            'general_notification_uid' => $generalNotification->uid,
        ]);
    }

    /**
     * @test
     * Prueba que devuelve correctamente la notificación general del usuario y la marca como leída
     */
    public function testGetGeneralNotificationNotExist()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Crear una notificación general en la base de datos
        GeneralNotificationsModel::factory()->create([
            'uid' => generate_uuid(),
        ])->first();

        // Hacer una solicitud GET a la ruta de la notificación general del usuario
        $response = $this->get('/notifications/general/get_general_notification_user/' . generate_uuid());

        // Verificar que la respuesta es exitosa
        $response->assertStatus(406);
        $response->assertJson([
            'message' => 'La notificación general no existe',
        ]);
    }

    /**
     * @test
     * Prueba que devuelve correctamente la notificación automática general del usuario y la marca como leída
     */
    public function testGetGeneralNotificationAutomaticUserMarksAsRead()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Verifica que el usuario se ha creado correctamente

        // Crear una notificación automática general y asociarla al usuario
        $generalNotificationAutomatic = GeneralNotificationsAutomaticModel::factory()->create([
            'uid' => generate_uuid(),
        ])->first();

        $generalNotificationAutomatic->users()->attach($user->uid, [
            'uid' => generate_uuid(),
            'is_read' => 0,
        ]);

        // Hacer una solicitud GET a la ruta de la notificación automática del usuario
        $response = $this->get('/notifications/general/get_general_notification_automatic_user/' . $generalNotificationAutomatic->uid);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que la notificación se ha marcado como leída
        $this->assertDatabaseHas('general_notifications_automatic_users', [
            'user_uid' => $user->uid,
            'general_notifications_automatic_uid' => $generalNotificationAutomatic->uid,
            'is_read' => 1,
        ]);
    }

    /**
     * @test
     * Prueba que devuelve un error 404 si la notificación automática no existe
     */
    public function testGetGeneralNotificationAutomaticUserReturnsErrorForNonExistingNotification()
    {
        // Buscamos un usuario
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo
        $this->actingAs($user);

        // Hacer una solicitud GET con un UID de notificación automática inexistente
        $response = $this->getJson('/notifications/general/get_general_notification_automatic_user/' . generate_uuid());

        // Verificar que la respuesta devuelve un error 404
        $response->assertStatus(404);

        // Verificar que el mensaje de error es el esperado en el JSON
        $response->assertJson([
            'message' => 'Notificación automática no encontrada',
        ]);
    }
}
