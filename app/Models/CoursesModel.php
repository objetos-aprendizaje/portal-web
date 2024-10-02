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
        'uid','title', 'description', 'course_type_uid', 'educational_program_type_uid',
        'call_uid', 'course_status_uid', 'min_required_students', 'center',
        'start_date', 'end_date', 'presentation_video_url', 'objectives', 'ects_workload',
        'validate_student_registrations', 'lms_url', 'cost','educational_program_uid', 'embeddings'
    ];

    public function average_calification()
    {
        return $this->hasOne(CoursesAssessmentsModel::class, 'course_uid')
            ->selectRaw('course_uid, ROUND(AVG(calification), 1) as average_calification')
            ->groupBy('course_uid');
    }

    public function status()
    {
        return $this->belongsTo(CourseStatusesModel::class, 'course_status_uid', 'uid');
    }

    public function teachers()
    {
        return $this->belongsToMany(
            UsersModel::class,
            'courses_teachers',
            'course_uid',
            'user_uid'
        )->withPivot('type');
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

    public function blocks()
    {
        return $this->hasMany(
            BlocksModel::class,
            'course_uid',
            'uid'
        );
    }

    public function course_type()
    {
        return $this->belongsTo(
            CourseTypesModel::class,
            'course_type_uid',
            'uid'
        );
    }

    public function contact_emails()
    {
        return $this->hasMany(
            CoursesEmailsContactsModel::class,
            'course_uid',
            'uid'
        );
    }

    public function educational_program_type()
    {
        return $this->belongsTo(
            EducationalProgramTypesModel::class,
            'educational_program_type_uid',
            'uid'
        );
    }

    public function course_documents()
    {
        return $this->hasMany(CourseDocumentsModel::class, 'course_uid', 'uid');
    }

    public function student_documents()
    {
        return $this->belongsToMany(
            CourseDocumentsModel::class,
            'courseS_students_documents',
            'course_document_uid',
            'uid',
            'uid',
            'course_uid'
        )->withPivot('user_uid', 'document_path');
    }

    public function students()
    {
        return $this->belongsToMany(
            UsersModel::class,
            'courses_students',
            'course_uid',
            'user_uid'
        )->withPivot(['acceptance_status', 'uid'])->as('course_student_info');
    }

    public function educationalProgram() {
        return $this->hasOne(EducationalProgramsModel::class, 'uid', 'educational_program_uid');
    }

    public function paymentTerms() {
        return $this->hasMany(
            CoursesPaymentTermsModel::class,
            'course_uid',
            'uid'
        )->orderBy('start_date');
    }
}
