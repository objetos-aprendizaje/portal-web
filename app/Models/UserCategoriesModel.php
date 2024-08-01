<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCategoriesModel extends Model
{
    use HasFactory;
    protected $table = 'user_categories';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = [
        'uid', 'user_uid', 'category_uid'
    ];
}
