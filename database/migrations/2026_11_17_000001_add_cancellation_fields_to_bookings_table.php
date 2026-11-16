<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vòng đời booking: lưu thông tin HỦY tách khỏi reject_reason (lý do TỪ CHỐI)
 * để hai nghiệp vụ không ghi đè nhau.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('reject_reason');
            $table->foreignId('cancelled_by')->nullable()->after('cancel_reason')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancel_reason', 'cancelled_by', 'cancelled_at', 'completed_at']);
        });
    }
};
