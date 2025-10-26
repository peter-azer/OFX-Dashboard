<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Team extends Model
{
    protected $fillable = [
        'member_name',
        'position',
        'bio',
        'member_name_ar',
        'position_ar',
        'bio_ar',
        'photo_url',
        'is_active',
    ];

    public function getPhotoUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
