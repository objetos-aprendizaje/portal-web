<?php

namespace App\Http\Controllers\Profile\Notifications;

use App\Models\AutomaticNotificationTypesModel;
use App\Models\NotificationsTypesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileEmailNotificationsController extends BaseController
{
    public function index()
    {
        $notificationTypes = NotificationsTypesModel::all();

        $user = Auth::user();

        $rolesUser = $user->roles()->get();

        $automaticNotificationTypes = AutomaticNotificationTypesModel::with('roles')
            ->whereHas('roles', function ($query) use ($rolesUser) {
                $query->whereIn('uid', $rolesUser->pluck("uid"));
            })
            ->get();

        return view('profile.notifications.email_notifications.index', [
            "resources" => [
                "resources/js/profile/notifications/email_notifications.js"
            ],
            'notification_types' => $notificationTypes,
            'user' => $user,
            'currentPage' => 'profileEmailNotifications',
            "page_title" => "Configuración de notificaciones por email",
            "automaticNotificationTypes" => $automaticNotificationTypes
        ]);
    }

    public function saveNotifications(Request $request)
    {
        $this->updateUserNotifications($request);
        $this->syncEmailNotificationTypes($request);
        $this->syncAutomaticEmailNotificationTypes($request);

        return response()->json([
            'message' => 'Notificaciones guardadas correctamente'
        ]);
    }

    private function updateUserNotifications(Request $request)
    {
        $user = auth()->user();
        $user->email_notifications_allowed = $request->input('email_notifications_allowed');
        $user->save();
    }

    private function syncEmailNotificationTypes(Request $request)
    {
        $emailNotificationTypesDisabled = $request->input('email_notification_types_disabled');
        $emailNotificationTypesSync = $this->prepareNotificationTypesSync($emailNotificationTypesDisabled);

        auth()->user()->emailNotificationsTypesDisabled()->sync($emailNotificationTypesSync);
    }

    private function prepareNotificationTypesSync($notificationTypes)
    {
        $notificationTypesSync = [];

        foreach ($notificationTypes as $notificationType) {
            $notificationTypesSync[] = [
                'uid' => generate_uuid(),
                'notification_type_uid' => $notificationType,
            ];
        }

        return $notificationTypesSync;
    }

    private function syncAutomaticEmailNotificationTypes(Request $request)
    {
        $automaticEmailNotificationTypesDisabled = $request->input('automatic_email_notification_types_disabled');
        $automaticEmailNotificationTypesSync = $this->prepareAutomaticNotificationTypesSync($automaticEmailNotificationTypesDisabled);

        auth()->user()->automaticEmailNotificationsTypesDisabled()->sync($automaticEmailNotificationTypesSync);
    }

    private function prepareAutomaticNotificationTypesSync($notificationTypes)
    {
        $notificationTypesSync = [];

        foreach ($notificationTypes as $notificationType) {
            $notificationTypesSync[] = [
                'uid' => generate_uuid(),
                'automatic_notification_type_uid' => $notificationType,
            ];
        }

        return $notificationTypesSync;
    }
}
