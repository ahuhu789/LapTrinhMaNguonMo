<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tăng cường toàn vẹn dữ liệu:
 *  1. stream_metrics: UNIQUE(streamer_id, platform, stream_date) - dọn bản ghi trùng trước (giữ bản mới nhất)
 *  2. schedules.reference_id: FK -> bookings.id (NULL khi booking bị xóa) - dọn tham chiếu mồ côi trước
 *  3. bookings.client_id: FK đổi từ CASCADE sang RESTRICT (không cho xóa client đang có booking)
 *
 * Chạy được trên MySQL và SQLite (Laravel 11 tự rebuild bảng SQLite khi thêm/xóa foreign key).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1a. Chuẩn hóa stream_date về 'Y-m-d' (SQLite có thể đang lưu '2026-10-01 00:00:00')
        if (DB::getDriverName() === 'sqlite') {
            DB::table('stream_metrics')->update(['stream_date' => DB::raw('date(stream_date)')]);
        }

        // 1b. Xóa bản ghi trùng, giữ lại bản có id lớn nhất của mỗi nhóm.
        //     Bọc subquery trong bảng dẫn xuất để MySQL cho phép DELETE trên cùng bảng.
        DB::table('stream_metrics')
            ->whereNotIn('id', function (Builder $query) {
                $query->select('keep_id')->fromSub(function (Builder $sub) {
                    $sub->from('stream_metrics')
                        ->selectRaw('MAX(id) as keep_id')
                        ->groupBy('streamer_id', 'platform', 'stream_date');
                }, 'keepers');
            })
            ->delete();

        Schema::table('stream_metrics', function (Blueprint $table) {
            $table->unique(['streamer_id', 'platform', 'stream_date'], 'stream_metrics_streamer_platform_date_unique');
        });

        // 2. Dọn reference_id trỏ tới booking không tồn tại rồi thêm FK
        DB::table('schedules')
            ->whereNotNull('reference_id')
            ->whereNotIn('reference_id', DB::table('bookings')->select('id'))
            ->update(['reference_id' => null]);

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('reference_id')->references('id')->on('bookings')->nullOnDelete();
        });

        // 3. bookings.client_id: CASCADE -> RESTRICT
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->foreign('client_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
        });

        Schema::table('stream_metrics', function (Blueprint $table) {
            $table->dropUnique('stream_metrics_streamer_platform_date_unique');
        });
    }
};
