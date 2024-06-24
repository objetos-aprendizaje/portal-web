<?php

namespace App\Http\Controllers\Profile;

use App\Models\AutomaticNotificationTypesModel;
use App\Models\NotificationsTypesModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends BaseController
{

    public function index()
    {
        $notification_types = NotificationsTypesModel::all();
        $automaticNotificationTypes = AutomaticNotificationTypesModel::all();
        $user = Auth::user();

        return view('profile.notifications.index', [
            "resources" => [
                "resources/js/profile/notifications.js"
            ],
            'notification_types' => $notification_types,
            'user' => $user,
            'currentPage' => 'notifications',
            "page_title" => "Configuración de notificaciones | POA",
            "automaticNotificationTypes" => $automaticNotificationTypes
        ]);
    }

    public function saveNotifications(Request $request)
    {
        $this->updateUserNotifications($request);
        $this->syncGeneralNotificationTypes($request);
        $this->syncEmailNotificationTypes($request);
        $this->syncAutomaticGeneralNotificationTypes($request);
        $this->syncAutomaticEmailNotificationTypes($request);

        return response()->json([
            'message' => 'Notificaciones guardadas correctamente'
        ]);
    }

    private function updateUserNotifications(Request $request)
    {
        $user = auth()->user();
        $user->general_notifications_allowed = $request->input('general_notifications_allowed');
        $user->email_notifications_allowed = $request->input('email_notifications_allowed');
        $user->save();
    }

    private function syncGeneralNotificationTypes(Request $request)
    {
        $general_notification_types_disabled = $request->input('general_notification_types_disabled');
        $general_notification_types_sync = $this->prepareNotificationTypesSync($general_notification_types_disabled);

        auth()->user()->generalNotificationsTypesDisabled()->sync($general_notification_types_sync);
    }

    private function syncEmailNotificationTypes(Request $request)
    {
        $email_notification_types_disabled = $request->input('email_notification_types_disabled');
        $email_notification_types_sync = $this->prepareNotificationTypesSync($email_notification_types_disabled);

        auth()->user()->emailNotificationsTypesDisabled()->sync($email_notification_types_sync);
    }

    private function prepareNotificationTypesSync($notification_types)
    {
        $notification_types_sync = [];

        foreach ($notification_types as $notification_type) {
            $notification_types_sync[] = [
                'uid' => generate_uuid(),
                'notification_type_uid' => $notification_type,
            ];
        }

        return $notification_types_sync;
    }

    private function syncAutomaticGeneralNotificationTypes(Request $request)
    {
        $automatic_general_notification_types_disabled = $request->input('automatic_general_notification_types_disabled');
        $automatic_general_notification_types_sync = $this->prepareAutomaticNotificationTypesSync($automatic_general_notification_types_disabled);

        auth()->user()->automaticGeneralNotificationsTypesDisabled()->sync($automatic_general_notification_types_sync);
    }

    private function syncAutomaticEmailNotificationTypes(Request $request)
    {
        $automatic_email_notification_types_disabled = $request->input('automatic_email_notification_types_disabled');
        $automatic_email_notification_types_sync = $this->prepareAutomaticNotificationTypesSync($automatic_email_notification_types_disabled);

        auth()->user()->automaticEmailNotificationsTypesDisabled()->sync($automatic_email_notification_types_sync);
    }

    private function prepareAutomaticNotificationTypesSync($notification_types)
    {
        $notification_types_sync = [];

        foreach ($notification_types as $notification_type) {
            $notification_types_sync[] = [
                'uid' => generate_uuid(),
                'automatic_notification_type_uid' => $notification_type,
            ];
        }

        return $notification_types_sync;
    }
}
