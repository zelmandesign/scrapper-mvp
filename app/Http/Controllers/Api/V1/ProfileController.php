<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Profile::query()
            ->orderByDesc('last_scraped_at');

        $perPage = (int)$request->get('per_page', 20);
        $perPage = max(1, min($perPage, 100)); // Clamp between 1 and 100

        return ProfileResource::collection(
            $query->paginate($perPage)
        );
    }

    public function show(string $handle): ProfileResource
    {
        $profile = Profile::where('username', $handle)->firstOrFail();
        return new ProfileResource($profile);
    }
}
