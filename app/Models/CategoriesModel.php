<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesModel extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    public function parentCategory()
    {
        return $this->belongsTo(CategoriesModel::class, 'parent_category_uid')->with('parentCategory')->whereNull('parent_category_uid');
    }

    public function subcategories()
    {
        return $this->hasMany(CategoriesModel::class, 'parent_category_uid', 'uid')->with('subcategories');
    }

    public function courses()
    {
        return $this->belongsToMany(CoursesModel::class, 'course_categories', 'category_uid', 'course_uid');
    }

    public function getCourseCountAttribute()
    {
        return $this->courses()->count();
    }
}
