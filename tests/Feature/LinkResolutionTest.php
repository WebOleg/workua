<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domain\Link\Models\Link;

class LinkResolutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_link_details()
    {
        $link = Link::create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123',
        ]);

        $response = $this->getJson("/api/links/{$link->short_code}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'short_code' => 'test123',
                         'original_url' => 'https://example.com'
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_link()
    {
        $response = $this->getJson('/api/links/nonexistent');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_redirects_to_original_url()
    {
        $link = Link::create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123',
        ]);

        $response = $this->get("/{$link->short_code}");

        $response->assertRedirect('https://example.com');
    }
}
