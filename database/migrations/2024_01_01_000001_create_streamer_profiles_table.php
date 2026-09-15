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
        Schema::create('streamer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('stage_name', 100);
            $table->string('category', 100)->default('Gaming');
            $table->text('bio')->nullable();
            $table->decimal('rate_per_hour', 12, 2)->default(500000);
            $table->string('avatar_url', 255)->nullable();
            $table->string('banner_url', 255)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();

            $table->index('category');
            $table->index('rate_per_hour');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streamer_profiles');
    }
};
