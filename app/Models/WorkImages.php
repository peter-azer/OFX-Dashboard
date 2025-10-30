<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkImages extends Model
{
    protected $table = 'work_images';

    protected $fillable = [
        'work_id',
        'image_url',
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }
}
