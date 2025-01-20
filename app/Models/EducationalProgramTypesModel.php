<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalProgramTypesModel extends Model
{
    use HasFactory;
    protected $table = 'educational_program_types';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'name', 'description', 'managers_can_emit_credentials', 'teachers_can_emit_credentials'
    ];

    public function redirection_queries() {
        return $this->hasMany(RedirectionQueriesLearningObjectsModel::class, 'educational_program_type_uid', 'uid');
    }
}
