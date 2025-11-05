<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Emails extends Model
{
    /** @use HasFactory<\Database\Factories\EmailsFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'is_active',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];
    /**
     * The services that belong to the email.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'email_service', 'email_id', 'service_id')
            ->withTimestamps();
    }

    public function emailSubmissions()
    {
        return $this->hasManyThrough(FormSubmition::class, 'email_id', 'id');
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
