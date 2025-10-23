<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_name',
        'short_description',
        'icon_url',
        'order',
        'is_active',
    ];
    //
}
