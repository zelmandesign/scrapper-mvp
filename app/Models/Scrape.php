<?php

namespace App\Models;

use Database\Factories\ScrapeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scrape extends Model
{
    /** @use HasFactory<ScrapeFactory> */
    use HasFactory;

    protected $fillable = [
        'username',
        'status',
        'idempotency_key',
        'webhook_url',
        'error_message',
    ];
}
