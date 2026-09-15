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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('streamer_id')->constrained('streamer_profiles')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('job_description');
            $table->decimal('budget', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(15.00);
            $table->enum('status', ['pending', 'reviewing', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->index(['streamer_id', 'status']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
