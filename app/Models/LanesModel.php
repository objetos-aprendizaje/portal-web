<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LanesModel extends Authenticatable
{

    use HasFactory;
    protected $table = 'lanes';
    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $fillable = ['uid', 'active', 'code'];

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

}
