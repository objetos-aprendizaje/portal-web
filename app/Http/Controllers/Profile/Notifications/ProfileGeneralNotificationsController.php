<?php

namespace App\Http\Controllers\Profile\Notifications;

use App\Models\AutomaticNotificationTypesModel;
use App\Models\NotificationsTypesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileGeneralNotificationsController extends BaseController
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

        return view('profile.notifications.general_notifications.index', [
            "resources" => [
                "resources/js/profile/notifications/general_notifications.js"
            ],
            'notification_types' => $notificationTypes,
            'user' => $user,
            'currentPage' => 'profileGeneralNotifications',
            "page_title" => "Configuración de notificaciones generales",
            "automaticNotificationTypes" => $automaticNotificationTypes
        ]);
    }

    public function saveNotifications(Request $request)
    {
        $this->updateUserNotifications($request);
        $this->syncGeneralNotificationTypes($request);
        $this->syncAutomaticGeneralNotificationTypes($request);

        return response()->json([
            'message' => 'Notificaciones guardadas correctamente'
        ]);
    }

    private function updateUserNotifications(Request $request)
    {
        $user = auth()->user();
        $user->general_notifications_allowed = $request->input('general_notifications_allowed');
        $user->save();
    }

    private function syncGeneralNotificationTypes(Request $request)
    {
        $generalNotificationTypesDisabled = $request->input('general_notification_types_disabled');
        $generalNotificationTypesSync = $this->prepareNotificationTypesSync($generalNotificationTypesDisabled);

        auth()->user()->generalNotificationsTypesDisabled()->sync($generalNotificationTypesSync);
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

    private function syncAutomaticGeneralNotificationTypes(Request $request)
    {
        $automaticGeneralNotificationTypesDisabled = $request->input('automatic_general_notification_types_disabled');
        $automaticGeneralNotificationTypesSync = $this->prepareAutomaticNotificationTypesSync($automaticGeneralNotificationTypesDisabled);

        auth()->user()->automaticGeneralNotificationsTypesDisabled()->sync($automaticGeneralNotificationTypesSync);
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
