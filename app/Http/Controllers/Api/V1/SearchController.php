<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = trim((string)$request->query('q', ''));
        if ($q === '') {
            abort(422, 'Query parameter "q" is required.');
        }

        $perPage = (int)$request->query('per_page', 20);
        $perPage = max(1, min($perPage, 50));

        $results = Profile::search($q)->paginate($perPage);
        $results->appends([
            'q' => $q,
            'per_page' => $perPage,
        ]);
        return ProfileResource::collection($results);
    }
}
