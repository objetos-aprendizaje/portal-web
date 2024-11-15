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

    public function categories()
    {
        return $this->belongsToMany(
            CategoriesModel::class,
            'educational_resource_categories',
            'educational_resource_uid',
            'category_uid'
        );
    }

    public function educationalResourceType()
    {
        return $this->belongsTo(EducationalResourceTypesModel::class, 'educational_resource_type_uid', 'uid');
    }

    public function contactEmails()
    {
        return $this->hasMany(
            EducationalResourceEmailsContactsModel::class,
            'educational_resource_uid',
            'uid'
        );
    }

    public function licenseType()
    {
        return $this->belongsTo(LicenseTypesModel::class, 'license_type_uid', 'uid');
    }

    public function learningResults()
    {
        return $this->belongsToMany(
            LearningResultsModel::class,
            'educational_resources_learning_results',
            'educational_resource_uid',
            'learning_result_uid'
        );
    }

    public function embeddings()
    {
        return $this->hasOne(EducationalResourcesEmbeddingsModel::class, 'educational_resource_uid', 'uid');
    }
}
