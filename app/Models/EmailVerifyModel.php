<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerifyModel extends Model
{

    use HasFactory;
    protected $table = 'email_verification_tokens';

    protected $fillable = [
        'user_uid', 'token'
    ];

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

}
