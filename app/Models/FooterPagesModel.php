<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterPagesModel extends Model
{
    use HasFactory;
    protected $table = 'footer_pages';
    protected $primaryKey = 'uid';
    protected $keyType = 'string';

    protected $fillable = ['name', 'content', 'slug', 'order', 'acceptance_required', 'version'];
}
