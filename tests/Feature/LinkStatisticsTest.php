<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domain\Link\Models\Link;
use App\Domain\Link\Models\LinkVisit;

class LinkStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_link_statistics()
    {
        $link = Link::create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123',
        ]);

        LinkVisit::create([
            'link_id' => $link->id,
            'ip_address' => '127.0.0.1',
            'visited_at' => now(),
        ]);

        $response = $this->getJson("/api/links/{$link->short_code}/statistics");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'link',
                         'statistics' => [
                             'total_visits',
                             'unique_visitors',
                         ]
                     ]
                 ]);
    }
}
