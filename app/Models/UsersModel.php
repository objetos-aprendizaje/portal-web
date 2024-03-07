<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = ['first_name', 'last_name', 'nif', 'email', 'user_rol_uid', 'curriculum'];

    public function rol()
    {
        return $this->belongsTo(UserRolesModel::class, 'user_rol_uid', 'uid');
    }


    public function roles()
    {
        return $this->belongsToMany(UserRolesModel::class, 'user_role_relationships', 'user_uid', 'user_role_uid')
            ->withPivot('uid', 'created_at', 'updated_at')
            ->withTimestamps();
    }

    public function coursesWithRoles()
    {
        return $this->belongsToMany(
            CoursesModel::class,
            'courses_users',
            'user_uid',
            'course_uid'
        )
        ->withPivot('user_rol_uid')
        ->withTimestamps()
        ->with(['pivot.role' => function ($query) {
            $query->select('uid', 'name')
                  ->from('user_roles')
                  ->whereColumn('courses_users.user_rol_uid', 'user_roles.uid');
        }]);
    }
}
