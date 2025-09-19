<?php

namespace App\Services\Scraping;

use App\Models\Scrape;

/**
 * ProfileScraper
 *
 * Fake scraper used for MVP.
 * Generates deterministic placeholder data instead of real scraping.
 */

class ProfileScraper implements ProfileScraperInterface
{
    public function __construct(public Scrape $scrape) {}
    public function scrape(string $username): array
    {
        return [
            'username' => $username,
            'name' => ucfirst($username),
            'bio' => 'Fake bio for ' . $username,
            'likes_count' => rand(0, 200000),
            'avatar_url' => 'https://picsum.photos/200',
            'scraped_at' => now(),
        ];
    }
}
