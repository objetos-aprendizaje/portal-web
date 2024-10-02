<?php

namespace App\Http\Middleware;

use App\Models\GeneralNotificationsAutomaticModel;
use App\Models\GeneralNotificationsAutomaticUsersModel;
use App\Models\GeneralNotificationsModel;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\UserGeneralNotificationsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Obtiene las notificaciones generales para el usuario
 * para poder mostrárselas en el header
 */
class GeneralNotificationsUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $generalNotifications = $this->getCombinedGeneralNotifications();

            $is_read_values = array_column($generalNotifications, 'is_read');
            $unread_general_notifications = in_array(0, $is_read_values);

            View::share('general_notifications', $generalNotifications);
            View::share('unread_general_notifications', $unread_general_notifications);
        }

        return $next($request);
    }

    // Combinamos las notificaciones generales y automáticas
    private function getCombinedGeneralNotifications()
    {

        $user = Auth::user();
        $userUid = $user->uid;
        $uidsRoles = $this->getUidsRoles($user);

        $generalNotificationsQuery = $this->buildGeneralNotificationsQuery($userUid, $uidsRoles);
        $generalNotificationsAutomaticQuery = $this->buildGeneralNotificationsAutomaticQuery($user->uid);
        $combinedQuery = $generalNotificationsQuery->union($generalNotificationsAutomaticQuery);
        $combinedQuery->orderBy('date', 'desc');
        return $combinedQuery->get()->toArray();
    }

    private function buildGeneralNotificationsQuery($userUid, $uidsRoles)
    {

        $generalNotificationsQuery = GeneralNotificationsModel::select([
            'general_notifications.uid',
            'general_notifications.title',
            'general_notifications.description',
            'general_notifications.start_date as date',
            DB::raw("'general_notification' as type")
        ])->with(['users', 'roles', 'users.generalNotificationsTypesDisabled'])
            ->where(function ($query) use ($userUid, $uidsRoles) {
                $this->applyUserFilter($query, $userUid)
                    ->orWhere(function ($q) use ($uidsRoles) {
                        $this->applyRoleFilter($q, $uidsRoles);
                    })
                    ->orWhere('type', 'ALL_USERS');
            })
            ->whereNotIn('notification_type_uid', function ($query) use ($userUid) {
                $query->select('notification_type_uid')
                    ->from('user_general_notification_types_disabled')
                    ->where('user_uid', $userUid);
            })
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->addSelect([
                'is_read' => UserGeneralNotificationsModel::select(DB::raw('CAST(CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END AS BOOLEAN)'))->whereColumn('user_general_notifications.general_notification_uid', 'general_notifications.uid')
                    ->where('user_general_notifications.user_uid', $userUid)
                    ->limit(1)
            ])
            ->orderBy('start_date', 'desc');

        return $generalNotificationsQuery;
    }

    private function buildGeneralNotificationsAutomaticQuery($userUid)
    {
        $generalNotificationsAutomaticQuery = GeneralNotificationsAutomaticModel::select([
            'general_notifications_automatic.uid',
            'general_notifications_automatic.title',
            'general_notifications_automatic.description',
            DB::raw("created_at as date"),
            DB::raw("'general_notification_automatic' as type")
        ])
            ->with('users')
            ->whereHas('users', function ($query) use ($userUid) {
                $query->where('user_uid', $userUid);
            })
            ->addSelect([
                'is_read' => GeneralNotificationsAutomaticUsersModel::select(DB::raw('is_read'))
                    ->whereColumn('general_notifications_automatic_users.general_notifications_automatic_uid', 'general_notifications_automatic.uid')
                    ->where('general_notifications_automatic_users.user_uid', $userUid)
                    ->limit(1)
            ]);

        return $generalNotificationsAutomaticQuery;
    }


    private function getUidsRoles($user)
    {
        return $user->roles->pluck('uid')->toArray();
    }

    private function applyUserFilter($query, $user_uid)
    {
        return $query->where(function ($q) use ($user_uid) {
            $q->where('type', 'USERS')
                ->whereHas('users', function ($query) use ($user_uid) {
                    $query->where('user_uid', $user_uid);
                });
        });
    }

    private function applyRoleFilter($query, $uids_roles)
    {
        return $query->where(function ($q) use ($uids_roles) {
            $q->where('type', 'ROLES')
                ->whereHas('roles', function ($query) use ($uids_roles) {
                    $query->whereIn('rol_uid', $uids_roles);
                });
        });
    }
}
