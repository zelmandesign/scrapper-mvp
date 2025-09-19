<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScrapeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'of_username' => ['required', 'string'],
            'webhook_url' => ['sometimes', 'string', 'url', 'regex:/^https:\/\//']
        ];
    }
}
