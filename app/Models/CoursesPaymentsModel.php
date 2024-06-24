<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoursesPaymentsModel extends Model
{
    use HasFactory;

    protected $table = 'courses_payments';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    protected $casts = [
        'uid' => 'string',
    ];

    public $incrementing = false;

    protected $fillable = [
        "uid",
        "user_uid",
        "course_uid",
        "order_number",
        "info",
        "is_paid"
    ];
}
