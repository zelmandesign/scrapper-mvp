<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScrapeRequest;
use App\Http\Resources\ScrapeResource;
use App\Jobs\ScrapeOfUser;
use App\Models\Scrape;
use Illuminate\Http\JsonResponse;

class ScrapeController extends Controller
{
    public function store(StoreScrapeRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if ($idempotencyKey && Scrape::where('idempotency_key', $idempotencyKey)->exists()) {
            return response()->json([
                'message' => 'Idempotency key already used.',
                'error'   => 'IDEMPOTENCY_KEY_CONFLICT'
            ], 409);
        }

        $scrape = Scrape::create([
            'idempotency_key' => $idempotencyKey,
            'username'        => $request->input('of_username'),
            'webhook_url'     => $request->input('webhook_url'),
        ]);

        ScrapeOfUser::dispatch($scrape->id);

        return response()->json([
            'message' => 'Processing scraping request',
            'payload' => $request->validated(),
            'scrape_id'   => $scrape->id,
            'status' => 'queued',
            'status_url'  => route('scrapes.show', $scrape->id),
        ], 202);
    }

    public function show(Scrape $scrape): ScrapeResource
    {
        return new ScrapeResource($scrape);
    }
}
