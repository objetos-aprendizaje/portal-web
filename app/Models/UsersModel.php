<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UsersModel extends Authenticatable
{
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = ['first_name', 'last_name', 'nif', 'email', 'user_rol_uid', 'curriculum', 'uid', 'verified', 'department_uid'];

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

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


    public function hasAnyRole(array $roles)
    {
        return !empty(array_intersect($roles, array_column($this->roles->toArray(), 'code')));
    }

    public function categories()
    {
        return $this->belongsToMany(
            CategoriesModel::class,
            'user_categories',
            'user_uid',
            'category_uid'
        );
    }

    public function generalNotificationsTypesDisabled()
    {
        return $this->belongsToMany(
            NotificationsTypesModel::class,
            'user_general_notification_types_disabled',
            'user_uid',
            'notification_type_uid'
        );
    }

    public function emailNotificationsTypesDisabled()
    {
        return $this->belongsToMany(
            NotificationsTypesModel::class,
            'user_email_notification_types_disabled',
            'user_uid',
            'notification_type_uid'
        );
    }

    public function courses_students()
    {
        return $this->belongsToMany(
            CoursesModel::class,
            'courses_students',
            'user_uid',
            'course_uid'
        )->withPivot("acceptance_status", "status");
    }

    public function educationalResources()
    {
        return $this->belongsToMany(
            EducationalResourcesModel::class,
            'educational_resource_access',
            'user_uid',
            'educational_resource_uid'
        );
    }

    public function educationalPrograms()
    {
        return $this->belongsToMany(
            EducationalProgramsModel::class,
            'educational_programs_students',
            'user_uid',
            'educational_program_uid'
        )->withPivot("acceptance_status", "status");
    }

    public function automaticGeneralNotificationsTypesDisabled()
    {
        return $this->belongsToMany(
            AutomaticNotificationTypesModel::class,
            'user_automatic_general_notification_types_disabled',
            'user_uid',
            'automatic_notification_type_uid'
        );
    }

    public function automaticEmailNotificationsTypesDisabled()
    {
        return $this->belongsToMany(
            AutomaticNotificationTypesModel::class,
            'user_email_automatic_notification_types_disabled',
            'user_uid',
            'automatic_notification_type_uid'
        );
    }

    public function learningResultsPreferences()
    {
        return $this->belongsToMany(
            LearningResultsModel::class,
            'user_learning_results_preferences',
            'user_uid',
            'learning_result_uid'
        );
    }

    public function userPoliciesAccepted()
    {
        return $this->belongsToMany(
            FooterPagesModel::class,
            'user_policies_accepted',
            'user_uid',
            'footer_page_uid'
        );
    }
}
