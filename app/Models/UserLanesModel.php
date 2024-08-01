<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserLanesModel extends Authenticatable
{

    use HasFactory;
    protected $table = 'user_lanes';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = ['uid', 'user_uid', 'active'];

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

}
