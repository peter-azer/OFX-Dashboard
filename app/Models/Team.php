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
        'photo_url',
        'facebook_link',
        'linkedin_link',
        'twitter_link',
        'is_active',
    ];

    public function getPhotoUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
