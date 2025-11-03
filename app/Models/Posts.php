<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Posts extends Model
{

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'author_id',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            $post->slug = Str::slug($post->title);
        });
    }

    // Example relationship if users exist
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
