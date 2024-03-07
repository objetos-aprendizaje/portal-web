<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalResourceStatusesModel extends Model
{
    use HasFactory;
    protected $table = 'educational_resource_statuses';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

}
