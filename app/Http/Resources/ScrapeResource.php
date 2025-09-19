<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'scrape_id'       => $this->id,
            'of_username'     => $this->username,
            'status'          => $this->status,
            'idempotency_key' => $this->idempotency_key,
            'webhook_url'     => $this->webhook_url,
            'error_message'   => $this->error_message,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
