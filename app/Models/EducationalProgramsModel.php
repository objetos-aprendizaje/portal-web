<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalProgramsModel extends Model
{
    use HasFactory;
    protected $table = 'educational_programs';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'uid','name', 'description', 'educational_program_type_uid', 'call_uid', 'inscription_start_date', 'inscription_finish_date','educational_program_status_uid'
    ];

    public function educational_program_type()
    {
        return $this->belongsTo(EducationalProgramTypesModel::class, 'educational_program_type_uid', 'uid');
    }

    public function courses()
    {
        return $this->hasMany(CoursesModel::class, 'educational_program_uid', 'uid');
    }

    public function valorations()
    {
        return $this->hasMany(EducationalProgramsAssessmentsModel::class, 'educational_program_uid', 'uid');
    }

    public function status()
    {
        return $this->belongsTo(EducationalProgramStatusesModel::class, 'educational_program_status_uid', 'uid');
    }

    public function students()
    {
        return $this->belongsToMany(
            UsersModel::class,
            'educational_programs_students',
            'educational_program_uid',
            'user_uid'
        )->withPivot(['acceptance_status', 'uid'])->as('educational_program_student_info');
    }


    public function educationalProgramDocuments()
    {
        return $this->hasMany(EducationalProgramsDocumentsModel::class, 'educational_program_uid', 'uid');
    }

    public function student_documents()
    {
        return $this->belongsToMany(
            EducationalProgramsDocumentsModel::class,
            'educational_programs_students_documents',
            'educational_program_document_uid',
            'uid',
            'uid',
            'educational_program_uid'
        )->withPivot('user_uid', 'document_path');
    }

    public function contact_emails()
    {
        return $this->hasMany(
            EducationalProgramsEmailContactsModel::class,
            'educational_program_uid',
            'uid'
        );
    }

    public function paymentTerms()
    {
        return $this->hasMany(
            EducationalProgramsPaymentTermsModel::class,
            'educational_program_uid',
            'uid'
        )->orderBy('start_date', 'asc');
    }
}
