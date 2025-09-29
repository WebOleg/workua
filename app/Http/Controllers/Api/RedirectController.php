<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Link\Models\Link;
use App\Domain\Link\Events\LinkVisited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * RedirectController
 * 
 * Handles short link redirects to original URLs
 */
class RedirectController extends Controller
{
    /**
     * Redirect short code to original URL
     * 
     * @param string $shortCode The short code to resolve
     * @param Request $request The HTTP request
     * @return RedirectResponse Redirect to original URL
     */
    public function redirect(string $shortCode, Request $request): RedirectResponse
    {
        $link = Link::where('short_code', $shortCode)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNull('deleted_at')
            ->first();

        if (!$link) {
            abort(404, 'Link not found');
        }

        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410, 'Link has expired');
        }

        event(new LinkVisited($link, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ]));

        return redirect()->away($link->original_url, 302);
    }
}
