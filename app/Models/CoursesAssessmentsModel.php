<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CoursesAssessmentsModel extends Model {

    use HasFactory;
    protected $table = 'courses_assessments';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'user_uid', 'calification', 'course_uid', 'uid'
    ];

    public $timestamps = false;


}
