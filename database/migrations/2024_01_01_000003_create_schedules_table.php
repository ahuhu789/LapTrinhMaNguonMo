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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streamer_id')->constrained('streamer_profiles')->onDelete('cascade');
            $table->enum('event_type', ['stream', 'booking', 'training', 'personal'])->default('stream');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Trỏ về booking_id nếu là lịch book');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('title', 150);
            $table->timestamps();

            $table->index(['streamer_id', 'start_time', 'end_time']);
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
