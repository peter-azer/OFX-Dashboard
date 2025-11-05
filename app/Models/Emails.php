<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    /** @use HasFactory<\Database\Factories\EmailsFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'is_active',
        'is_main',
    ];

    /**
     * The services that belong to the email.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'email_service', 'email_id', 'service_id')
                   ->withTimestamps();
    }
}
