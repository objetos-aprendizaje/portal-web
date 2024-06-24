<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CoursesStudentsDocumentsModel extends Authenticatable
{
    use HasFactory;
    protected $table = 'courses_students_documents';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

    protected $fillable = [
        'uid', 'user_uid', 'course_document_uid', 'document_path'
    ];
}
