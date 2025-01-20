<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalProgramsEmailContactsModel extends Model
{
    use HasFactory;

    protected $table = 'educational_programs_email_contacts';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    protected $fillable = ['uid', 'educational_program_uid', 'email'];

    public $incrementing = false;

    public $timestamps = false;
}
