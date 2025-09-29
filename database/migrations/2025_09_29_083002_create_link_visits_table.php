<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the link_visits table for tracking analytics and statistics
     */
    public function up(): void
    {
        Schema::create('link_visits', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to links table
            $table->foreignId('link_id')
                ->constrained('links')
                ->onDelete('cascade')
                ->comment('Reference to shortened link');
            
            // Visitor information for analytics
            $table->string('ip_address', 45)->nullable()->comment('Visitor IP address (IPv4/IPv6)');
            $table->text('user_agent')->nullable()->comment('Browser user agent string');
            $table->string('referer', 2048)->nullable()->comment('Referring URL');
            
            // Geographic and device information
            $table->string('country', 2)->nullable()->comment('ISO country code');
            $table->string('city', 100)->nullable()->comment('City name');
            $table->string('device_type', 20)->nullable()->comment('Device type: mobile/desktop/tablet');
            $table->string('browser', 50)->nullable()->comment('Browser name');
            $table->string('os', 50)->nullable()->comment('Operating system');
            
            // Visit timestamp
            $table->timestamp('visited_at')->useCurrent()->comment('Time of visit');
            
            // Indexes for analytics queries
            $table->index('link_id', 'idx_link_id');
            $table->index('visited_at', 'idx_visited_at');
            $table->index(['link_id', 'visited_at'], 'idx_link_visits');
            $table->index('ip_address', 'idx_ip_address');
            $table->index('country', 'idx_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_visits');
    }
};
