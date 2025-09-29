<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Link\Services\LinkShortenerService;
use App\Domain\Link\Services\LinkResolverService;
use App\Domain\Link\Services\StatisticsService;
use App\Http\Requests\CreateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Domain\Link\Exceptions\LinkNotFoundException;
use App\Domain\Link\Exceptions\LinkExpiredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * LinkController
 * 
 * Handles HTTP requests for link operations
 */
class LinkController extends Controller
{
    /**
     * Create a new controller instance
     * 
     * @param LinkShortenerService $shortenerService Service for creating short links
     * @param LinkResolverService $resolverService Service for resolving short codes
     * @param StatisticsService $statisticsService Service for analytics
     */
    public function __construct(
        private readonly LinkShortenerService $shortenerService,
        private readonly LinkResolverService $resolverService,
        private readonly StatisticsService $statisticsService
    ) {}

    /**
     * Create a new shortened link
     * 
     * @param CreateLinkRequest $request Validated request data
     * @return JsonResponse Created link resource
     */
    public function store(CreateLinkRequest $request): JsonResponse
    {
        Log::channel('structured')->info('Link creation started', [
            'url' => $request->input('url'),
            'custom_code' => $request->input('custom_code'),
            'ip' => $request->ip(),
        ]);

        try {
            $link = $this->shortenerService->shorten(
                $request->input('url'),
                $request->input('ttl_minutes'),
                $request->input('custom_code')
            );

            Log::channel('structured')->info('Link created successfully', [
                'link_id' => $link->id,
                'short_code' => $link->short_code,
            ]);

            return response()->json([
                'success' => true,
                'data' => new LinkResource($link),
                'message' => 'Link created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::channel('structured')->error('Link creation failed', [
                'error' => $e->getMessage(),
                'url' => $request->input('url'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get link details by short code
     * 
     * @param string $shortCode The short code to lookup
     * @return JsonResponse Link details
     */
    public function show(string $shortCode): JsonResponse
    {
        try {
            $link = $this->resolverService->getLinkDetails($shortCode);

            return response()->json([
                'success' => true,
                'data' => new LinkResource($link)
            ]);

        } catch (LinkNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);

        } catch (LinkExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 410);
        }
    }

    /**
     * Get statistics for a link
     * 
     * @param string $shortCode The short code to get stats for
     * @return JsonResponse Link statistics
     */
    public function statistics(string $shortCode): JsonResponse
    {
        try {
            $link = $this->resolverService->getLinkDetails($shortCode);
            $stats = $this->statisticsService->getStatistics($link);

            return response()->json([
                'success' => true,
                'data' => [
                    'link' => new LinkResource($link),
                    'statistics' => $stats
                ]
            ]);

        } catch (LinkNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
