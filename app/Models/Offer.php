<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_name',
        'offer_name_ar',
        'short_description',
        'short_description_ar',
        'image_url',
        'order',
        'is_active',
    ];
}
