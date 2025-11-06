<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitors extends Model
{
    /** @use HasFactory<\Database\Factories\VisitorsFactory> */
    use HasFactory;

        protected $fillable = [
        'ip_address',
        'url',
        'path',
        'method',
        'user_agent',
        'referrer',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'is_bot',
    ];
}
