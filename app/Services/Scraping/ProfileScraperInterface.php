<?php

namespace App\Services\Scraping;

interface ProfileScraperInterface
{
    public function scrape(string $username): array;
}
