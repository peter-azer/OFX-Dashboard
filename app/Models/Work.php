<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

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

    public function getProjectImageAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
