<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Brand extends Model
{
    protected $fillable = [
        'brand_name',
        'brand_name_ar',
        'logo_url',
        'order',
        'is_active',
    ];

    public function getLogoUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
//
