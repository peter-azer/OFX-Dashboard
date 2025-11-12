<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneContacts extends Model
{
    /** @use HasFactory<\Database\Factories\PhoneContactsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'counter'
    ];

    public function records()
    {
        return $this->hasMany(PhoneRecord::class);
    }
}
