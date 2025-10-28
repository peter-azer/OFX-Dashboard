<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneRecord extends Model
{
    protected $fillable = [
        'phone_contact_id',
    ];

    public function phoneContact()
    {
        return $this->belongsTo(PhoneContacts::class, 'phone_contact_id');
    }
}
