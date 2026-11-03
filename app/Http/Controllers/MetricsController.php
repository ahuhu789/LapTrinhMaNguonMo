<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadMetricsCsvRequest;
use App\Services\CsvMetricsService;
use App\Models\StreamMetric;
use App\Models\StreamerProfile;
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
    public function uploadCsv(UploadMetricsCsvRequest $request, CsvMetricsService $csvService)
    {
        $user = $request->user();

        // Admin được nhập cho mọi streamer, Manager chỉ cho các streamer mình phụ trách
        $allowedStreamerIds = $user->isAdmin()
            ? StreamerProfile::pluck('id')->all()
            : $user->managedStreamers()->pluck('id')->all();

        $result = $csvService->importCsv($request->file('csv_file')->getRealPath(), $allowedStreamerIds);

        // Lỗi cấp tệp -> chỉ flash 'error' (không kèm import_errors để tránh hiển thị trùng)
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Thành công -> flash 'success'; lỗi từng dòng (nếu có) -> chỉ flash 'import_errors'
        $response = back()->with('success', $result['message']);

        if (!empty($result['errors'])) {
            $response->with('import_errors', $result['errors']);
        }

        return $response;
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
