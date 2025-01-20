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
    public function getGeneralNotificationUser($notificationGeneralUid)
    {
        $userUid = Auth::user()['uid'];

        $generalNotification = GeneralNotificationsModel::where('uid', $notificationGeneralUid)->addSelect([
            'is_read' => UserGeneralNotificationsModel::select(DB::raw('CAST(CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END AS INTEGER)'))
                ->whereColumn('user_general_notifications.general_notification_uid', 'general_notifications.uid')
                ->where('user_general_notifications.user_uid', $userUid)
                ->limit(1)
        ])
            ->first();

        if (!$generalNotification) {
            return response()->json(['message' => 'La notificación general no existe'], 406);
        }

        // La marcamos como vista
        if (!$generalNotification['is_read']) {
            $userGeneralNotification = new UserGeneralNotificationsModel();
            $userGeneralNotification->uid = generate_uuid();
            $userGeneralNotification->user_uid = $userUid;
            $userGeneralNotification->general_notification_uid = $generalNotification['uid'];
            $userGeneralNotification->view_date = date('Y-m-d H:i:s');

            $userGeneralNotification->save();
        }

        $generalNotificationMapped = [
            "uid" => $generalNotification->uid,
            "title" => $generalNotification->title,
            "description" => $generalNotification->description,
            "start_date" => $generalNotification->start_date,
            "end_date" => $generalNotification->end_date,
        ];

        return response()->json($generalNotificationMapped, 200);
    }

    public function getGeneralNotificationAutomaticUser($generalNotificationAutomaticUid)
    {
        $userUid = Auth::user()['uid'];

        $generalNotificationAutomatic = GeneralNotificationsAutomaticModel::where('uid', $generalNotificationAutomaticUid)
            ->whereHas('users', function ($query) use ($userUid) {
                $query->where('users.uid', $userUid);
            })
            ->first();

        if (!$generalNotificationAutomatic) {
            abort(404, 'Notificación automática no encontrada');
        }

        // Marcamos como vista la notificación al usuario
        if ($generalNotificationAutomatic) {
            GeneralNotificationsAutomaticUsersModel::where('general_notifications_automatic_uid', $generalNotificationAutomatic->uid)
                ->where('user_uid', $userUid)
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
