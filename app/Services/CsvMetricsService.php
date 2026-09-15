<?php

namespace App\Services;

use App\Models\StreamMetric;
use App\Models\StreamerProfile;
use App\Models\Kpi;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CsvMetricsService
{
    protected array $validPlatforms = ['youtube', 'twitch', 'tiktok', 'facebook'];

    /**
     * Parse và import file CSV báo cáo số liệu stream
     *
     * @param string $filePath Đường dẫn tuyệt đối tới file CSV
     * @return array Kết quả chi tiết quá trình xử lý
     */
    public function importCsv(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'message' => 'Không thể đọc tệp CSV tải lên.',
                'imported_count' => 0,
                'errors' => ['Tệp tin không tồn tại hoặc không thể đọc.'],
            ];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Không thể mở tệp tin CSV.',
                'imported_count' => 0,
                'errors' => ['Lỗi mở luồng đọc file.'],
            ];
        }

        // 1. Đọc header dòng đầu tiên
        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'Tệp CSV rỗng.',
                'imported_count' => 0,
                'errors' => ['File không có dữ liệu.'],
            ];
        }

        // Chuẩn hóa tên cột (loại bỏ khoảng trắng, chữ thường, BOM)
        $header = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col)));
        }, $header);

        $expectedColumns = ['streamer_id', 'platform', 'stream_date', 'duration_hours', 'avg_viewers', 'peak_viewers', 'followers_gained'];
        foreach ($expectedColumns as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return [
                    'success' => false,
                    'message' => 'Định dạng cột CSV không hợp lệ. Cột thiếu: ' . $col,
                    'imported_count' => 0,
                    'errors' => ['Yêu cầu đầy đủ các cột: ' . implode(', ', $expectedColumns)],
                ];
            }
        }

        $colMap = array_flip($header);
        $importedCount = 0;
        $rowNumber = 1;
        $errors = [];
        $affectedStreamerMonths = []; // Lưu lại danh sách ['streamer_id' => X, 'month_year' => 'YYYY-MM'] để tái tính KPI

        // Lấy danh sách streamer_id hợp lệ trong hệ thống để tra cứu nhanh O(1)
        $validStreamerIds = StreamerProfile::pluck('id')->flip()->toArray();

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;

                // Bỏ qua dòng trống
                if (empty(array_filter($row))) {
                    continue;
                }

                $streamerId = isset($row[$colMap['streamer_id']]) ? (int) trim($row[$colMap['streamer_id']]) : 0;
                $platform = isset($row[$colMap['platform']]) ? strtolower(trim($row[$colMap['platform']])) : '';
                $streamDate = isset($row[$colMap['stream_date']]) ? trim($row[$colMap['stream_date']]) : '';
                $duration = isset($row[$colMap['duration_hours']]) ? (float) trim($row[$colMap['duration_hours']]) : 0;
                $avgViewers = isset($row[$colMap['avg_viewers']]) ? (int) trim($row[$colMap['avg_viewers']]) : 0;
                $peakViewers = isset($row[$colMap['peak_viewers']]) ? (int) trim($row[$colMap['peak_viewers']]) : 0;
                $followers = isset($row[$colMap['followers_gained']]) ? (int) trim($row[$colMap['followers_gained']]) : 0;

                // Kiểm tra hợp lệ dữ liệu từng dòng
                if (!isset($validStreamerIds[$streamerId])) {
                    $errors[] = "Dòng $rowNumber: Streamer ID ($streamerId) không tồn tại.";
                    continue;
                }

                if (!in_array($platform, $this->validPlatforms)) {
                    $errors[] = "Dòng $rowNumber: Platform '$platform' không hợp lệ (hỗ trợ: youtube, twitch, tiktok, facebook).";
                    continue;
                }

                try {
                    $dateObj = Carbon::parse($streamDate);
                } catch (Exception $e) {
                    $errors[] = "Dòng $rowNumber: Định dạng ngày '$streamDate' không đúng (chuẩn YYYY-MM-DD).";
                    continue;
                }

                if ($duration <= 0 || $duration > 24) {
                    $errors[] = "Dòng $rowNumber: Thời lượng ($duration giờ) không hợp lý (phải từ 0.1 đến 24h).";
                    continue;
                }

                // Ghi vào bảng stream_metrics
                StreamMetric::create([
                    'streamer_id' => $streamerId,
                    'platform' => $platform,
                    'stream_date' => $dateObj->toDateString(),
                    'duration_hours' => $duration,
                    'avg_viewers' => max(0, $avgViewers),
                    'peak_viewers' => max(0, $peakViewers),
                    'followers_gained' => $followers,
                    'source_type' => 'csv_import',
                ]);

                $importedCount++;

                // Đánh dấu tháng cần aggregate
                $monthYear = $dateObj->format('Y-m');
                $key = "{$streamerId}_{$monthYear}";
                $affectedStreamerMonths[$key] = [
                    'streamer_id' => $streamerId,
                    'month_year' => $monthYear,
                ];
            }

            fclose($handle);

            // 2. Tự động kích hoạt tiến trình cộng dồn cập nhật bảng KPIs
            $updatedKpisCount = $this->aggregateKpis($affectedStreamerMonths);

            DB::commit();

            return [
                'success' => true,
                'message' => "Import thành công $importedCount bản ghi metrics. Đã cập nhật $updatedKpisCount chỉ tiêu KPI liên quan.",
                'imported_count' => $importedCount,
                'updated_kpis' => $updatedKpisCount,
                'errors' => $errors,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            return [
                'success' => false,
                'message' => 'Lỗi xử lý cơ sở dữ liệu: ' . $e->getMessage(),
                'imported_count' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Tự động tính toán tổng số giờ và doanh thu để cập nhật KPI
     *
     * @param array $targets Mảng các cặp ['streamer_id' => X, 'month_year' => 'YYYY-MM']
     * @return int Số lượng bản ghi KPI đã được cập nhật
     */
    public function aggregateKpis(array $targets): int
    {
        $updatedCount = 0;

        foreach ($targets as $item) {
            $streamerId = $item['streamer_id'];
            $monthYear = $item['month_year'];

            // 1. Tính tổng số giờ stream trong tháng
            $totalHours = StreamMetric::where('streamer_id', $streamerId)
                ->whereRaw("DATE_FORMAT(stream_date, '%Y-%m') = ?", [$monthYear])
                ->sum('duration_hours');

            // 2. Tính tổng doanh thu từ các booking đã duyệt/hoàn thành trong tháng
            $totalRevenue = Booking::where('streamer_id', $streamerId)
                ->whereIn('status', ['approved', 'completed'])
                ->whereRaw("DATE_FORMAT(start_time, '%Y-%m') = ?", [$monthYear])
                ->sum('budget');

            // 3. Tìm hoặc tạo bản ghi KPI tương ứng
            $kpi = Kpi::where('streamer_id', $streamerId)
                ->where('month_year', $monthYear)
                ->first();

            if ($kpi) {
                $kpi->achieved_hours = (float) $totalHours;
                $kpi->achieved_revenue = (float) $totalRevenue;

                // Xác định trạng thái
                if ($kpi->achieved_hours >= $kpi->target_hours && $kpi->achieved_revenue >= $kpi->target_revenue) {
                    $kpi->status = 'achieved';
                } else {
                    $kpi->status = 'in_progress';
                }

                $kpi->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
