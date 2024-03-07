<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesValorationsModel extends Model
{
    use HasFactory;
    protected $table = 'courses_valorations';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'course_uid', 'valoration'
    ];

}
