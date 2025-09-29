<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Link\Models\Link;
use App\Domain\Link\Models\LinkVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirect(string $shortCode, Request $request): RedirectResponse
    {
        // Find active link
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

        // Record visit
        LinkVisit::create([
            'link_id' => $link->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'visited_at' => now(),
        ]);

        return redirect()->away($link->original_url, 301);
    }
}
