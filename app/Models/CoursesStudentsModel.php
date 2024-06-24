<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoursesStudentsModel extends Model
{

    use HasFactory;

    protected $table = 'courses_students';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

    protected $fillable = [
        "uid",
        "user_uid",
        "course_uid",
        "calification_type",
        "calification",
        "acceptance_status",
        "credential"
    ];
}
