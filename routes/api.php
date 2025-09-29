<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\RedirectController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::get('/health', function () {
    $services = [
        'database' => checkDatabase(),
        'redis' => checkRedis(),
        'queue' => checkQueue(),
    ];

    $allHealthy = collect($services)->every(fn($s) => $s['status'] === 'ok');

    return response()->json([
        'status' => $allHealthy ? 'ok' : 'degraded',
        'timestamp' => now()->toIso8601String(),
        'services' => $services,
    ], $allHealthy ? 200 : 503);
});

if (!function_exists('checkDatabase')) {
    function checkDatabase(): array {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);
            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

if (!function_exists('checkRedis')) {
    function checkRedis(): array {
        try {
            Redis::ping();
            return ['status' => 'ok'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

if (!function_exists('checkQueue')) {
    function checkQueue(): array {
        try {
            $size = Redis::llen('queues:default');
            return ['status' => 'ok', 'pending_jobs' => $size];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

Route::prefix('links')->group(function () {
    Route::post('/', [LinkController::class, 'store']);
    Route::get('/{shortCode}', [LinkController::class, 'show']);
    Route::get('/{shortCode}/statistics', [LinkController::class, 'statistics']);
});
