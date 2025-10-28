<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppRecord extends Model
{
    
    protected $fillable = [
        'whats_app_contacts_id',
    ];

    public function whatsAppContact()
    {
        return $this->belongsTo(WhatsAppContacts::class, 'whats_app_contact_id');
    }
}
