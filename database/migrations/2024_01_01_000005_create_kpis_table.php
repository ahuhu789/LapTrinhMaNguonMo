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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streamer_id')->constrained('streamer_profiles')->onDelete('cascade');
            $table->char('month_year', 7)->comment('YYYY-MM');
            $table->decimal('target_hours', 6, 2)->default(60.00);
            $table->decimal('target_revenue', 12, 2)->default(10000000);
            $table->integer('target_avg_viewers')->default(1000);
            $table->decimal('achieved_hours', 6, 2)->default(0);
            $table->decimal('achieved_revenue', 12, 2)->default(0);
            $table->enum('status', ['in_progress', 'achieved', 'failed'])->default('in_progress');
            $table->timestamps();

            $table->unique(['streamer_id', 'month_year']);
            $table->index('month_year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
