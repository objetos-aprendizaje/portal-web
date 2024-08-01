<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLearningResultsPreferencesModel extends Model
{
    use HasFactory;
    protected $table = 'user_learning_results_preferences';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

}

