<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\UserRolesModel;
use App\Models\NotificationsTypesModel;
use App\Models\GeneralNotificationsModel;
use App\Models\AutomaticNotificationTypesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationsControllerTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     * Prueba que la vista de notificaciones del perfil se carga correctamente con los tipos de notificaciones y notificaciones automáticas
     */
    public function testIndexLoadsNotificationPageWithCorrectData()
    {
        // Crear un usuario y autenticarlo
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

        $roles = $user->roles()->get();

        // Crear tipos de notificaciones
        NotificationsTypesModel::factory()->count(3)->create();

        $notificationTypes = NotificationsTypesModel::get();

        // Crear notificaciones automáticas y asociarlas a los roles del usuario
        $automaticNotificationTypes = AutomaticNotificationTypesModel::get();

        foreach ($automaticNotificationTypes as $notification) {
            $notification->roles()->attach($roles->pluck('uid')->toArray());
        }

        // dd($automaticNotificationTypes);
        // Hacer una solicitud GET a la ruta de notificaciones del perfil
        $response = $this->get(route('notifications'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se carga la vista correcta
        $response->assertViewIs('profile.notifications.index');

        // Verificar que los datos se pasan a la vista
        // Verificar que los tipos de notificaciones existen en la vista, verificando los uids
        $response->assertViewHas('notification_types', function ($viewNotificationTypes) use ($notificationTypes) {
            return $viewNotificationTypes->pluck('uid')->sort()->values()->all() === $notificationTypes->pluck('uid')->sort()->values()->all();
        });
        // Verificar que los tipos de notificaciones automáticas existen en la vista, verificando los uids
        // $response->assertViewHas('automaticNotificationTypes', $automaticNotificationTypes);
        $response->assertViewHas('user', $user);
        $response->assertViewHas('currentPage', 'notifications');
        $response->assertViewHas('page_title', 'Configuración de notificaciones');
    }


    /**
     * @test
     * Prueba que se guardan correctamente las preferencias de notificaciones del usuario
     */
    public function testSaveNotificationsUpdatesUserPreferences()
    {
        // Crear un usuario y autenticarlo
        // Buscar un usuario y autenticarlo          
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        //Si no existe el usuario lo creamos
        if(!$user){
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();            
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        // Crear algunos tipos de notificaciones y notificaciones automáticas
        NotificationsTypesModel::factory()->count(3)->create();
        $notificationTypes = NotificationsTypesModel::get();

        $automaticNotificationTypes = AutomaticNotificationTypesModel::get();

        // Datos simulados enviados desde el formulario de notificaciones
        $requestData = [
            'general_notifications_allowed' => true,
            'email_notifications_allowed' => true,
            'general_notification_types_disabled' => $notificationTypes->pluck('uid')->toArray(),
            'email_notification_types_disabled' => [],
            'automatic_general_notification_types_disabled' => $automaticNotificationTypes->pluck('uid')->toArray(),
            'automatic_email_notification_types_disabled' => [],
        ];

        // Hacer la solicitud POST a la ruta de guardar notificaciones
        $response = $this->post(route('save-notifications'), $requestData);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que el mensaje correcto se devuelve en el JSON
        $response->assertJson(['message' => 'Notificaciones guardadas correctamente']);

        // Verificar que las preferencias del usuario se han actualizado correctamente
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'general_notifications_allowed' => true,
            'email_notifications_allowed' => true,
        ]);

        // Verificar que los tipos de notificaciones generales deshabilitadas se han sincronizado
        // foreach ($notificationTypes as $type) {
        //     $this->assertDatabaseHas('general_notifications_types_disabled', [
        //         'user_uid' => $user->uid,
        //         'notification_type_uid' => $type->uid,
        //     ]);
        // }

        // Verificar que los tipos de notificaciones automáticas generales deshabilitadas se han sincronizado
        // foreach ($automaticNotificationTypes as $type) {
        //     $this->assertDatabaseHas('user_automatic_general_notification_types_disabled', [
        //         'user_uid' => $user->uid,
        //         'automatic_notification_type_uid' => $type->uid,
        //     ]);
        // }
    }




}
