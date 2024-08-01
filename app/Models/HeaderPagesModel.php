<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class HeaderPagesModel extends Authenticatable
{
    use HasFactory;
    protected $table = 'header_pages';
    protected $primaryKey = 'uid';
    protected $keyType = 'string';

    protected $fillable = ['name', 'content', 'header_page_uid'];

    public function headerPagesChildren()
    {
        return $this->hasMany(HeaderPagesModel::class, 'header_page_uid', 'uid')->orderBy('order', 'asc');
    }
}
