<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class About extends Model
{
    protected $fillable = [
        'title',
        'description',
        'title_ar',
        'description_ar',
        'image_url',
        'video_url',
        'is_active',
    ];

    public function getImageUrlAttribute($value)
    {
        if (!$value) return null;
        return preg_match('/^https?:\/\//i', $value) ? $value : url($value);
    }
}
