<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stream_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streamer_id')->constrained('streamer_profiles')->onDelete('cascade');
            $table->enum('platform', ['youtube', 'twitch', 'tiktok', 'facebook'])->default('youtube');
            $table->date('stream_date');
            $table->decimal('duration_hours', 5, 2)->default(0);
            $table->integer('avg_viewers')->default(0);
            $table->integer('peak_viewers')->default(0);
            $table->integer('followers_gained')->default(0);
            $table->enum('source_type', ['manual_entry', 'csv_import', 'mock_api'])->default('manual_entry');
            $table->timestamps();

            $table->index(['streamer_id', 'stream_date']);
            $table->index('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_metrics');
    }
};
