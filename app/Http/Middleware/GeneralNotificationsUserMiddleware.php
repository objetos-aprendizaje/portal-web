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

            $isReadValues = array_column($generalNotifications, 'is_read');
            $unreadGeneralNotifications = in_array(0, $isReadValues);

            View::share('general_notifications', $generalNotifications);
            View::share('unread_general_notifications', $unreadGeneralNotifications);
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

        return GeneralNotificationsModel::select([
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
    }

    private function buildGeneralNotificationsAutomaticQuery($userUid)
    {
        return GeneralNotificationsAutomaticModel::select([
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
      
    }


    private function getUidsRoles($user)
    {
        return $user->roles->pluck('uid')->toArray();
    }

    private function applyUserFilter($query, $userUid)
    {
        return $query->where(function ($q) use ($userUid) {
            $q->where('type', 'USERS')
                ->whereHas('users', function ($query) use ($userUid) {
                    $query->where('user_uid', $userUid);
                });
        });
    }

    private function applyRoleFilter($query, $uidsRoles)
    {
        return $query->where(function ($q) use ($uidsRoles) {
            $q->where('type', 'ROLES')
                ->whereHas('roles', function ($query) use ($uidsRoles) {
                    $query->whereIn('rol_uid', $uidsRoles);
                });
        });
    }
}
