<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = Profile::query()
            ->orderByDesc('last_scraped_at');

        return ProfileResource::collection(
            $query->paginate((int)($request->get('per_page', 20)))
        );
    }

    public function show(string $handle)
    {
        $profile = Profile::where('username', $handle)->firstOrFail();
        return new ProfileResource($profile);
    }
}
