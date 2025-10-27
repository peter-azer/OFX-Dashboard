<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppContacts extends Model
{
    /** @use HasFactory<\Database\Factories\WhatsAppContactsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone'
    ];

    public function records()
    {
        return $this->hasMany(WhatsAppRecord::class);
    }
}
