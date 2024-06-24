<?php

namespace App\Http\Controllers;

use App\Models\GeneralNotificationsAutomaticModel;
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
            'is_read' => UserGeneralNotificationsModel::select(DB::raw('IF(COUNT(*), 1, 0)'))
                ->whereColumn('user_general_notifications.general_notification_uid', 'general_notifications.uid')
                ->where('user_general_notifications.user_uid', $user_uid)
                ->limit(1)
        ])
            ->first()->toArray();

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

        return response()->json($general_notification, 200);
    }

    public function getGeneralNotificationAutomaticUser($generalNotificationAutomaticUid)
    {
        $user_uid = Auth::user()['uid'];

        $generalNotificationAutomatic = GeneralNotificationsAutomaticModel::where('uid', $generalNotificationAutomaticUid)
            ->with(['users' => function ($query) use ($user_uid) {
                $query->where('users.uid', $user_uid);
            }])
            ->whereHas('users', function ($query) use ($user_uid) {
                $query->where('users.uid', $user_uid);
            })
            ->first();

        if (!$generalNotificationAutomatic) abort(404, 'Notificación automática no encontrada');

        // Marcamos como vista la notificación al usuario
        if ($generalNotificationAutomatic) {
            $generalNotificationAutomatic->users[0]->pivot->is_read = 1;
            $generalNotificationAutomatic->users[0]->pivot->save();
        }

        return response()->json($generalNotificationAutomatic->toArray(), 200);
    }
}
