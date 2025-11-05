<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FormSubmition extends Model
{
    /** @use HasFactory<\Database\Factories\FormSubmitionFactory> */
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'message',
    ];



    public function services()
    {
        return $this->belongsToMany(Service::class, 'form_service', 'form_id', 'service_id')
                    ->withTimestamps();
    }
}
