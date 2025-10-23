<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = [
        'project_title',
        'project_description',
        'project_image',
        'project_link',
        'category',
        'is_active',
    ];
    //
}
