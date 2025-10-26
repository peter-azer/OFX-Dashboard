<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'short_description',
        'service_name_ar',
        'short_description_ar',
        'icon_url',
        'order',
        'is_active',
    ];

    public function getIconUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
//
