<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScrapeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'of_username' => ['required', 'string'],
            'webhook_url' => ['sometimes', 'string', 'url', 'regex:/^https:\/\//']
        ];
    }
}
