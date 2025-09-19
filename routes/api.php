<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ScrapeController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World',
    ]);
});

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('v1')->group(function () {
    Route::get('/profiles', [ProfileController::class, 'index']);
    Route::get('/profiles/{handle}', [ProfileController::class, 'show'])
    ->where('handle', '[A-Za-z0-9_.-]+');
    Route::get('/search', [SearchController::class, 'index']);

    Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
        Route::post('/scrapes', [ScrapeController::class, 'store']);
        Route::get('/scrapes/{scrape}', [ScrapeController::class, 'show'])->name('scrapes.show');
    });
});
