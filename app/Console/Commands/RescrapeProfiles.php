<?php

namespace App\Console\Commands;

use App\Jobs\RescrapeOfUser;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class RescrapeProfiles extends Command
{
    private const PROFILE_THRESHOLD = 100_000;
    private const CHUNK_SIZE = 500;

    protected $signature = 'profiles:rescrape {--tier=high}';
    protected $description = 'Dispatch re-scrape jobs for profiles by tier';

    public function handle(): int
    {
        $tier = strtolower((string)$this->option('tier'));
        if (!in_array($tier, ['high', 'normal'], true)) {
            $this->error('Invalid --tier. Use "high" or "normal".');
            return self::FAILURE;
        }

        $this->info("Rescrape START — tier={$tier}");
        $dispatched = 0;

        $this->getProfilesQuery($tier)->chunkById(self::CHUNK_SIZE, function ($profiles) use (&$dispatched) {
            foreach ($profiles as $profile) {
                RescrapeOfUser::dispatch($profile->id);
                $dispatched++;
            }
        });

        $this->info("Rescrape END — tier={$tier}, dispatched={$dispatched}");
        return self::SUCCESS;
    }

    private function getProfilesQuery(string $tier): Builder
    {
        $threshold = self::PROFILE_THRESHOLD;

        return Profile::query()
            ->when($tier === 'high', fn($q) => $q->where('likes_count', '>=', $threshold))
            ->when($tier === 'normal', fn($q) => $q->where('likes_count', '<', $threshold))
            ->orderBy('id');
    }
}
