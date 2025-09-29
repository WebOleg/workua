<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\RedirectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Link management endpoints
Route::prefix('links')->group(function () {
    // Create shortened link
    Route::post('/', [LinkController::class, 'store']);
    
    // Get link details
    Route::get('/{shortCode}', [LinkController::class, 'show']);
    
    // Get link statistics
    Route::get('/{shortCode}/statistics', [LinkController::class, 'statistics']);
});
