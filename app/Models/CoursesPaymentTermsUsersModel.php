<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesPaymentTermsUsersModel extends Model
{
    use HasFactory;

    protected $table = 'courses_payment_terms_users';

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['course_payment_term_uid', 'user_uid', 'payment_date', 'info', 'is_paid', 'order_number', 'uid'];

    public $timestamps = false;
}
