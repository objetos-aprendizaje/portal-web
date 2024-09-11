<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class UserPoliciesAcceptedModel extends Authenticatable
{
    use HasFactory;
    protected $table = 'user_policies_accepted';
    protected $primaryKey = 'uid';
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['footer_page_uid', 'user_uid', 'version', 'accepted_at'];

}
