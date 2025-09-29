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

/**
 * LinkController
 * 
 * Handles HTTP requests for link operations
 */
class LinkController extends Controller
{
    /**
     * Creates a new controller instance
     * 
     * @param LinkShortenerService $shortenerService
     * @param LinkResolverService $resolverService
     * @param StatisticsService $statisticsService
     */
    public function __construct(
        private readonly LinkShortenerService $shortenerService,
        private readonly LinkResolverService $resolverService,
        private readonly StatisticsService $statisticsService
    ) {}

    /**
     * Creates a new shortened link
     * 
     * @param CreateLinkRequest $request
     * @return JsonResponse
     */
    public function store(CreateLinkRequest $request): JsonResponse
    {
        try {
            $link = $this->shortenerService->shorten(
                $request->input('url'),
                $request->input('ttl_minutes'),
                $request->input('custom_code')
            );

            return response()->json([
                'success' => true,
                'data' => new LinkResource($link),
                'message' => 'Link created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Gets link details by short code
     * 
     * @param string $shortCode
     * @return JsonResponse
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
     * Gets statistics for a link
     * 
     * @param string $shortCode
     * @return JsonResponse
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
