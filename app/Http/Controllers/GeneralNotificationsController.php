<?php

namespace App\Http\Controllers;

use App\Models\GeneralNotificationsAutomaticModel;
use App\Models\GeneralNotificationsAutomaticUsersModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\GeneralNotificationsModel;
use App\Models\UserGeneralNotificationsModel;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class GeneralNotificationsController extends BaseController
{

    use AuthorizesRequests, ValidatesRequests;

    /**
     * Devuelve una notificación general para un usuario y además la marca como vista
     */
    public function getGeneralNotificationUser($notification_general_uid)
    {

        if (!$notification_general_uid) {
            return response()->json(['message' => env('ERROR_MESSAGE')], 400);
        }

        $user_uid = Auth::user()['uid'];

        $general_notification = GeneralNotificationsModel::where('uid', $notification_general_uid)->addSelect([
            'is_read' => UserGeneralNotificationsModel::select(DB::raw('CAST(CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END AS INTEGER)'))
                ->whereColumn('user_general_notifications.general_notification_uid', 'general_notifications.uid')
                ->where('user_general_notifications.user_uid', $user_uid)
                ->limit(1)
        ])
            ->first();

        if (!$general_notification) {
            return response()->json(['message' => 'La notificación general no existe'], 406);
        }

        // La marcamos como vista
        if (!$general_notification['is_read']) {
            $user_general_notification = new UserGeneralNotificationsModel();
            $user_general_notification->uid = generate_uuid();
            $user_general_notification->user_uid = $user_uid;
            $user_general_notification->general_notification_uid = $general_notification['uid'];
            $user_general_notification->view_date = date('Y-m-d H:i:s');

            $user_general_notification->save();
        }

        $generalNotificationMapped = [
            "uid" => $general_notification->uid,
            "title" => $general_notification->title,
            "description" => $general_notification->description,
            "start_date" => $general_notification->start_date,
            "end_date" => $general_notification->end_date,
        ];

        return response()->json($generalNotificationMapped, 200);
    }

    public function getGeneralNotificationAutomaticUser($generalNotificationAutomaticUid)
    {
        $user_uid = Auth::user()['uid'];

        $generalNotificationAutomatic = GeneralNotificationsAutomaticModel::where('uid', $generalNotificationAutomaticUid)
            ->whereHas('users', function ($query) use ($user_uid) {
                $query->where('users.uid', $user_uid);
            })
            ->first();

        if (!$generalNotificationAutomatic) abort(404, 'Notificación automática no encontrada');

        // Marcamos como vista la notificación al usuario
        if ($generalNotificationAutomatic) {
            GeneralNotificationsAutomaticUsersModel::where('general_notifications_automatic_uid', $generalNotificationAutomatic->uid)
                ->where('user_uid', $user_uid)
                ->update(['is_read' => true]);
        }

        $generalNotificationAutomatic = [
            "title" => $generalNotificationAutomatic->title,
            "description" => $generalNotificationAutomatic->description,
            "entity" => $generalNotificationAutomatic->entity
        ];

        return response()->json($generalNotificationAutomatic, 200);
    }
}
