<?php

namespace App\Jobs;

use App\Events\ScrapeFinalized;
use App\Models\Profile;
use App\Models\Scrape;
use App\Services\Scraping\ProfileScraper; // <- concrete (no binding needed)
use App\Services\Scraping\ProfileScraperInterface;
use Illuminate\Bus\Queueable;                 // <- correct Queueable
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;        // optional but fine
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScrapeOfUser implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 60];

    public function __construct(public int $scrapRequestID)
    {
        $this->onQueue('scraping');
    }

    public function handle(): void
    {
        /** @var Scrape|null $scrape */
        $scrape = Scrape::find($this->scrapRequestID);

        if (!$scrape) {
            Log::warning("[scrape:{$this->scrapRequestID}] Not found, skipping.");
            return;
        }

        if (in_array($scrape->status, ['succeeded', 'failed'], true)) {
            Log::info("[scrape:{$scrape->id}] Already {$scrape->status}, skipping.");
            return;
        }

        Log::info("[scrape:start] scrape_id={$scrape->id}, username={$scrape->username}");

        $scrape->update(['status' => 'running', 'error_message' => null]);

        /** @var ProfileScraperInterface $scraper */
        $scraper = app(ProfileScraper::class);

        try {
            $data = $scraper->scrape($scrape->username);

            DB::transaction(function () use ($scrape, $data) {
                /** @var Profile $profile */
                $profile = Profile::updateOrCreate(
                    ['username' => $data['username']],
                    [
                        'name'            => $data['name']         ?? null,
                        'bio'             => $data['bio']          ?? null,
                        'likes_count'     => (int)($data['likes_count'] ?? 0),
                        'avatar_url'      => $data['avatar_url']   ?? null,
                        'last_scraped_at' => $data['scraped_at']   ?? now(),
                    ]
                );

                $scrape->update([
                    'status'        => 'succeeded',
                    'profile_id'    => $profile->id,
                    'error_message' => null,
                ]);
            });

            Log::info("[scrape:succeeded] scrape_id={$scrape->id}, username={$scrape->username}, profile_id={$scrape->profile_id}");

            if (!empty($scrape->webhook_url)) {
                Log::info("[scrape:webhook_dispatch] scrape_id={$scrape->id}, url={$scrape->webhook_url}");
                SendWebhook::dispatch($scrape->id);
            }

        } catch (Throwable $e) {
            Log::error("[scrape:{$scrape->id}] Failed: {$e->getMessage()}");

            if ($this->attempts() >= $this->tries) {
                $scrape->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                if (!empty($scrape->webhook_url)) {
                    Log::error("[scrape:failed] scrape_id={$scrape->id}, username={$scrape->username}, error={$e->getMessage()}");
                    SendWebhook::dispatch($scrape->id);
                }
                return;
            }

            throw $e;
        }
    }
}
