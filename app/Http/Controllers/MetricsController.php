<?php

namespace App\Http\Controllers;

use App\Services\CsvMetricsService;
use App\Models\StreamMetric;
use App\Models\StreamerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetricsController extends Controller
{
    /**
     * Màn hình Upload CSV và Quản lý Số liệu Stream
     */
    public function showUploadForm()
    {
        $user = Auth::user();

        // Lấy danh sách metrics gần đây
        $query = StreamMetric::with('streamer')->orderBy('stream_date', 'desc');

        if ($user->isManager()) {
            $streamerIds = $user->managedStreamers()->pluck('id');
            $metrics = $query->whereIn('streamer_id', $streamerIds)->paginate(20);
        } else {
            $metrics = $query->paginate(20);
        }

        return view('manager.metrics_upload', compact('metrics'));
    }

    /**
     * Xử lý file CSV tải lên và kích hoạt cập nhật KPI
     */
    public function uploadCsv(Request $request, CsvMetricsService $csvService)
    {
        $request->validate([
            'csv_file' => 'required|file|max:5120', // Tối đa 5MB
        ], [
            'csv_file.required' => 'Vui lòng chọn tệp CSV cần tải lên.',
            'csv_file.file' => 'Tệp tải lên không hợp lệ.',
            'csv_file.max' => 'Dung lượng tệp không được vượt quá 5MB.',
        ]);

        $file = $request->file('csv_file');

        // Kiểm tra phần mở rộng tệp
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return back()->with('error', 'Chỉ chấp nhận tệp tin có định dạng .csv hoặc .txt.');
        }

        $tempPath = $file->getRealPath();
        $result = $csvService->importCsv($tempPath);

        if (!$result['success']) {
            return back()->with('error', $result['message'])->with('import_errors', $result['errors'] ?? []);
        }

        $msg = $result['message'];
        return back()->with('success', $msg)->with('import_errors', $result['errors'] ?? []);
    }

    /**
     * Tải về file CSV mẫu chuẩn
     */
    public function downloadSample()
    {
        $filePath = database_path('sample_metrics.csv');
        if (!file_exists($filePath)) {
            abort(404, 'Không tìm thấy file mẫu.');
        }

        return response()->download($filePath, 'sample_stream_metrics.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
