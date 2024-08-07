<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomaticNotificationTypesModel extends Model
{
    use HasFactory;
    protected $table = 'automatic_notification_types';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    public function roles() {
        return $this->belongsToMany(UserRolesModel::class, 'automatic_notification_types_roles_relationship', 'automatic_notification_type_uid', 'user_role_uid');
    }
}
