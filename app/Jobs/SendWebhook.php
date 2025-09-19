<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scrape;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $scrapeId
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        $scrape = Scrape::find($this->scrapeId);

        if (!$scrape || !$scrape->webhook_url) {
            Log::warning("[scrape:{$this->scrapeId}] No webhook URL, skipping.");
            return;
        }

        try {
            Http::post($scrape->webhook_url, [
                'scrape_id' => $scrape->id,
                'username'  => $scrape->username,
                'status'    => $scrape->status,
                'error'     => $scrape->error_message,
                'updated_at'=> $scrape->updated_at,
            ]);

            Log::info("[scrape:{$scrape->id}] Webhook sent to {$scrape->webhook_url}");
        } catch (\Throwable $e) {
            Log::error("[scrape:{$scrape->id}] Webhook failed: {$e->getMessage()}");
            throw $e;
        }
    }
}
