<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Work extends Model
{
    use HasSlug;
    protected $fillable = [
        'project_title',
        'slug',
        'project_description',
        'service_id',
        'project_title_ar',
        'project_description_ar',
        'project_image',
        'project_link',
        'is_active',
        'order',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('project_title')
            ->saveSlugsTo('slug');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function getProjectImageAttribute($value)
    {
        return $value ? url($value) : null;
    }
    public function images()
    {
        return $this->hasMany(WorkImages::class, 'work_id');
    }
}
