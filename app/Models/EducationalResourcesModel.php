<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalResourcesModel extends Model
{
    use HasFactory;
    protected $table = 'educational_resources';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'name', 'description', 'image_path', 'resource_path',
        'status_uid', 'educational_resource_type_uid', 'license_type', 'resource_way',
        'resource_url'
    ];

    public function status()
    {
        return $this->belongsTo(EducationalResourceStatusesModel::class, 'status_uid', 'uid');
    }
}
