<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'username','name','bio','likes_count','avatar_url','last_scraped_at',
    ];

    protected $casts = [
        'last_scraped_at' => 'datetime',
    ];

    public function toSearchableArray(): array
    {
        return [
            'username' => $this->username,
            'name'     => $this->name,
            'bio'      => $this->bio,
        ];
    }
}
