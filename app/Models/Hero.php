<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Hero extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'image_url',
        'order',
        'is_active',
    ];

    public function getImageUrlAttribute($value)
    {
        if (!$value) return null;
        return preg_match('/^https?:\/\//i', $value) ? $value : url($value);
    }
}
