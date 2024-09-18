<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalResourceEmailsContactsModel extends Model
{
    use HasFactory;

    protected $table = 'educational_resources_email_contacts';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $timestamps = false;

    protected $fillable = ['uid', 'educational_resource_uid', 'email'];

    public $incrementing = false;
}
