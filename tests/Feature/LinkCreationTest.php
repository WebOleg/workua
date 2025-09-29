<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Domain\Link\Models\Link;

class LinkCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_shortened_link()
    {
        $response = $this->postJson('/api/links', [
            'url' => 'https://example.com'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'original_url',
                         'short_code',
                         'short_url',
                     ],
                     'message'
                 ]);

        $this->assertDatabaseHas('links', [
            'original_url' => 'https://example.com'
        ]);
    }

    /** @test */
    public function it_validates_url_format()
    {
        $response = $this->postJson('/api/links', [
            'url' => 'not-a-valid-url'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('url');
    }

    /** @test */
    public function it_can_create_link_with_custom_code()
    {
        $response = $this->postJson('/api/links', [
            'url' => 'https://example.com',
            'custom_code' => 'custom'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('links', [
            'short_code' => 'custom'
        ]);
    }

    /** @test */
    public function it_can_create_link_with_expiration()
    {
        $response = $this->postJson('/api/links', [
            'url' => 'https://example.com',
            'ttl_minutes' => 60
        ]);

        $response->assertStatus(201);
        
        $link = Link::where('original_url', 'https://example.com')->first();
        $this->assertNotNull($link->expires_at);
    }
}
