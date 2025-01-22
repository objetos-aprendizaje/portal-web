<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\NotificationsTypesModel;
use App\Models\AutomaticNotificationTypesModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileEmailNotificationsControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @test
     * Prueba que la vista de notificaciones del perfil
     */
    public function testIndexLoadsProfileEmailNotifications()
    {
        // Crear un usuario y autenticarlo
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
        $response = $this->get(route('profile-email-notifications'));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que se carga la vista correcta
        $response->assertViewIs('profile.notifications.email_notifications.index');

        // Verificar que los datos se pasan a la vista
        // Verificar que los tipos de notificaciones existen en la vista, verificando los uids
        $response->assertViewHas('notification_types', function ($viewNotificationTypes) use ($notificationTypes) {
            return $viewNotificationTypes->pluck('uid')->sort()->values()->all() === $notificationTypes->pluck('uid')->sort()->values()->all();
        });
        // Verificar que los tipos de notificaciones automáticas existen en la vista, verificando los uids
        // $response->assertViewHas('automaticNotificationTypes', $automaticNotificationTypes);
        $response->assertViewHas('user', $user);
        $response->assertViewHas('currentPage', 'profileEmailNotifications');
        $response->assertViewHas('page_title', 'Configuración de notificaciones por email');
        $response->assertViewHas('automaticNotificationTypes', $automaticNotificationTypes);
       
    }


     /**
     * @test
     * Prueba que se guardan correctamente las preferencias de notificaciones del usuario
     */
    public function testSaveNotificationsProfileEmailNotifications()
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
            'email_notification_types_disabled' => $notificationTypes->pluck('uid')->toArray(),            
            'automatic_email_notification_types_disabled' => $automaticNotificationTypes->pluck('uid')->toArray(),
        ];

        // Hacer la solicitud POST a la ruta de guardar notificaciones
        $response = $this->post('/profile/notifications/email/save', $requestData);

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

       
    }
}
