<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'short_description',
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
