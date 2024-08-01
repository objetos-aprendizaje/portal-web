<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalProgramsPaymentsModel extends Model
{
    use HasFactory;

    protected $table = 'educational_programs_payments';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

    protected $fillable = [
        "uid",
        "user_uid",
        "educational_program_uid",
        "order_number",
        "info",
        "is_paid"
    ];
}
