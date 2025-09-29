<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the links table for storing shortened URLs with expiration support
     */
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            
            // Original URL that was shortened
            $table->string('original_url', 2048)->comment('Original long URL');
            
            // Unique short code for the URL (e.g., "abc123")
            $table->string('short_code', 10)->unique()->comment('Unique short identifier');
            
            // Optional expiration timestamp
            $table->timestamp('expires_at')->nullable()->comment('Link expiration time');
            
            // Timestamps for record tracking
            $table->timestamps();
            
            // Soft deletes for data retention
            $table->softDeletes();
            
            // Indexes for performance optimization
            $table->index('short_code', 'idx_short_code');
            $table->index('expires_at', 'idx_expires_at');
            $table->index(['expires_at', 'deleted_at'], 'idx_active_links');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
