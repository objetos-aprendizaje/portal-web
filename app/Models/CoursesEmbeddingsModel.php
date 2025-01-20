<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoursesEmbeddingsModel extends Model
{
    use HasFactory;
    protected $table = 'courses_embeddings';
    protected $primaryKey = 'course_uid';

    protected $keyType = 'string';
    protected $casts = [
        'course_uid' => 'string',
        'embeddings' => 'array'
    ];

    public $incrementing = false;

    protected $fillable = [
        'course_uid',
        'embeddings',
    ];
    

}
