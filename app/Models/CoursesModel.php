<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesModel extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'title', 'description', 'course_type_uid', 'educational_program_type_uid',
        'call_uid', 'course_status_uid', 'min_required_students', 'center',
        'start_date', 'end_date', 'presentation_video_url', 'objectives', 'ects_workload',
        'validate_student_registrations', 'lms_url', 'cost'
    ];


    public function status()
    {
        return $this->belongsTo(CourseStatusesModel::class, 'course_status_uid', 'uid');
    }

    public function teachers()
    {
        $teacherRoleUid = UserRolesModel::where('code', 'TEACHER')->first()->uid;

        return $this->belongsToMany(
            UsersModel::class,
            'courses_users',
            'course_uid',
            'user_uid'
        )->wherePivot('user_rol_uid', $teacherRoleUid);
    }

    public function valorations()
    {
        return $this->hasMany(CoursesValorationsModel::class, 'course_uid', 'uid');
    }

    public function averageValoration()
    {
        return $this->valorations()->avg('valoration');
    }

    public function tags()
    {
        return $this->hasMany(
            CoursesTagsModel::class,
            'course_uid',
            'uid'
        );
    }

    public function categories()
    {
        return $this->belongsToMany(
            CategoriesModel::class,
            'course_categories',
            'course_uid',
            'category_uid'
        );
    }
}
