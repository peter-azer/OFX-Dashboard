<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notifiable;


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

    /**
     * The emails that belong to the service.
     */
    public function emails()
    {
        return $this->belongsToMany(Emails::class, 'email_service', 'service_id', 'email_id')
                   ->withTimestamps();
    }

    public function formSubmissions()
    {
        return $this->belongsToMany(FormSubmition::class, 'form_service', 'service_id', 'form_id')
                    ->withTimestamps();
    }

    public function work(){
        return $this->hasMany(Work::class);
    }
    public function getIconUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
//
