<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesAccessesModel extends Model
{
    use HasFactory;

    protected $table = 'courses_accesses';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'uid', 'course_uid', 'user_uid', 'access_date'
    ];

    public $timestamps = false;
}
