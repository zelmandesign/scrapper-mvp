<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'username','name','bio','likes_count','avatar_url','last_scraped_at',
    ];

    protected $casts = [
        'last_scraped_at' => 'datetime',
    ];

    // If using Scout:
    // use \Laravel\Scout\Searchable;
    // public function toSearchableArray(): array {
    //     return ['username' => $this->username, 'name' => $this->name, 'bio' => $this->bio];
    // }
}
