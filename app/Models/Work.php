<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Work extends Model
{
    protected $fillable = [
        'project_title',
        'project_description',
        'service_id',
        'project_title_ar',
        'project_description_ar',
        'project_image',
        'project_link',
        'is_active',
    ];

    public function service(){
        return $this->belongsTo(Service::class);
    }
    public function getProjectImageAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
