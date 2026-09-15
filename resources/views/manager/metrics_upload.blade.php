@extends('layouts.admin')

@section('title', 'Nhập Số Liệu Livestream CSV - MCN Platform')
@section('page_title', 'Nhập & Quản Lý Số Liệu Livestream (Metrics)')

@section('content')
<div class="space-y-6">
    <!-- Top Upload Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="font-bold text-slate-900 text-sm mb-2 flex items-center space-x-2">
                <i class="fas fa-file-arrow-up text-primary-600"></i>
                <span>Tải Lên Tệp Tin CSV Số Liệu Stream</span>
            </h3>
            <p class="text-xs text-slate-500 mb-4">
                Hệ thống sẽ tự động phân tích (CSV Parser), ghi vào bảng <code>stream_metrics</code> và kích hoạt tiến trình cộng dồn cập nhật bảng <code>kpis</code> cho tháng tương ứng.
            </p>

            <form action="{{ route('manager.metrics.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-primary-500 transition cursor-pointer bg-slate-50/50">
                    <i class="fas fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-700">Chọn tệp CSV từ máy tính của bạn</p>
                    <p class="text-[11px] text-slate-400 mt-1">Định dạng file .csv, tối đa 5MB</p>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required class="mt-3 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('manager.metrics.sample') }}" class="text-xs font-bold text-indigo-600 hover:underline flex items-center space-x-1">
                        <i class="fas fa-download"></i>
                        <span>Tải về file CSV mẫu chuẩn</span>
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition">
                        Bắt Đầu Import & Cập Nhật KPI
                    </button>
                </div>
            </form>
        </div>

        <!-- CSV Format Instructions -->
        <div class="bg-slate-900 text-slate-300 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h4 class="font-bold text-white text-sm mb-3 flex items-center space-x-2">
                    <i class="fas fa-circle-info text-primary-400"></i>
                    <span>Cấu Trúc Cột Yêu Cầu</span>
                </h4>
                <p class="text-xs text-slate-400 mb-3">Tệp CSV cần có dòng tiêu đề chuẩn xác sau:</p>
                <div class="bg-slate-950 p-3 rounded-xl font-mono text-[11px] text-emerald-400 border border-slate-800 break-all mb-4">
                    streamer_id, platform, stream_date, duration_hours, avg_viewers, peak_viewers, followers_gained
                </div>
                <ul class="text-xs text-slate-400 space-y-1.5 list-disc pl-4">
                    <li><strong class="text-slate-200">platform:</strong> youtube, twitch, tiktok, facebook</li>
                    <li><strong class="text-slate-200">stream_date:</strong> YYYY-MM-DD</li>
                    <li><strong class="text-slate-200">duration_hours:</strong> Số thực (VD: 4.5)</li>
                </ul>
            </div>
            <div class="text-[11px] text-slate-400 border-t border-slate-800 pt-3 mt-4">
                * Tốc độ xử lý: &lt; 3 giây cho tệp 500 dòng theo tiêu chuẩn nghiệm thu môn học.
            </div>
        </div>
    </div>

    <!-- Error Logs (if any occurred during CSV parsing) -->
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-rose-800">
            <h4 class="font-bold text-xs uppercase tracking-wider mb-2 flex items-center">
                <i class="fas fa-triangle-exclamation mr-1.5 text-rose-600"></i> Cảnh báo các dòng không hợp lệ:
            </h4>
            <ul class="list-disc pl-5 text-xs space-y-1">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Recent Metrics Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 text-sm">Lịch Sử Số Liệu Metrics Đã Ghi Nhận</h3>
            <span class="text-xs text-slate-500">Mới nhất lên đầu</span>
        </div>

        @if($metrics->isEmpty())
            <div class="p-12 text-center text-slate-400 text-xs">
                Chưa có bản ghi số liệu nào.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Talent Streamer</th>
                            <th class="py-3 px-4">Nền Tảng</th>
                            <th class="py-3 px-4">Ngày Phát Sóng</th>
                            <th class="py-3 px-4">Thời Lượng</th>
                            <th class="py-3 px-4">Avg Viewers</th>
                            <th class="py-3 px-4">Peak Viewers</th>
                            <th class="py-3 px-4">Followers Mới</th>
                            <th class="py-3 px-4">Nguồn Dữ Liệu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($metrics as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $m->streamer->stage_name }}</td>
                                <td class="py-3 px-4 capitalize flex items-center space-x-1.5">
                                    <i class="{{ $m->platform_icon }}"></i>
                                    <span>{{ $m->platform }}</span>
                                </td>
                                <td class="py-3 px-4">{{ $m->stream_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">{{ $m->duration_hours }} giờ</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($m->avg_viewers) }}</td>
                                <td class="py-3 px-4 font-bold text-indigo-600">{{ number_format($m->peak_viewers) }}</td>
                                <td class="py-3 px-4 text-emerald-600 font-semibold">+{{ number_format($m->followers_gained) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ $m->source_type }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $metrics->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
