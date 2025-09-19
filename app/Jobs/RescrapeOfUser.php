<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Services\Scraping\ProfileScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RescrapeOfUser implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 60, 180];

    public function __construct(public int $profileId)
    {
        $this->onQueue('rescraping');
    }

    public function handle(): void
    {
        /** @var Profile|null $profile */
        $profile = Profile::find($this->profileId);

        if (!$profile) {
            Log::warning("[rescrape:{$this->profileId}] Profile not found, skipping.");
            return;
        }

        Log::info("[rescrape:start] profile_id={$profile->id}, username={$profile->username}");

        $scraper = app(ProfileScraper::class);

        try {
            $data = $scraper->scrape($profile->username);

            DB::transaction(function () use ($profile, $data) {
                $profile->update([
                    'name'            => $data['name']         ?? $profile->name,
                    'bio'             => $data['bio']          ?? $profile->bio,
                    'likes_count'     => (int)($data['likes_count'] ?? $profile->likes_count),
                    'avatar_url'      => $data['avatar_url']   ?? $profile->avatar_url,
                    'last_scraped_at' => $data['scraped_at']   ?? now(),
                ]);
            });

            Log::info("[rescrape:succeeded] profile_id={$profile->id}, username={$profile->username}");
        } catch (Throwable $e) {
            Log::error("[rescrape:failed] profile_id={$profile->id}, username={$profile->username}, error={$e->getMessage()}");

            if ($this->attempts() >= $this->tries) {
                return;
            }

            throw $e;
        }
    }
}
