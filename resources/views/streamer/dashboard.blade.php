@extends('layouts.streamer')

@section('title', 'Streamer Workspace - ' . $profile->stage_name)
@section('page_title', 'Bảng Điều Khiển Cá Nhân - ' . $profile->stage_name)

@section('content')
<div class="space-y-6">
    <!-- Streamer Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-md flex flex-col sm:flex-row items-center sm:items-start justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-5 text-center sm:text-left">
            <img src="{{ $profile->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200' }}" class="w-20 h-20 rounded-2xl border-2 border-emerald-500/50 shadow-lg object-cover">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    {{ $profile->category }}
                </span>
                <h2 class="text-2xl font-black mt-1">{{ $profile->stage_name }}</h2>
                <p class="text-xs text-slate-300 mt-1">
                    Quản lý phụ trách: <span class="font-bold text-white">{{ $profile->manager->name ?? 'Đang chỉ định' }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('streamer.profile') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 transition">
                <i class="fas fa-edit mr-1"></i> Sửa Media Kit
            </a>
            <a href="{{ route('talents.show', $profile->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                <i class="fas fa-eye mr-1"></i> Xem Trang Công Khai
            </a>
        </div>
    </div>

    <!-- Monthly KPI Tracker -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <i class="fas fa-bullseye text-emerald-600"></i>
                    <span>Chỉ Tiêu KPI Tháng Này ({{ \Carbon\Carbon::now()->format('m/Y') }})</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Dữ liệu được cập nhật tự động qua các buổi stream và booking đã hoàn tất.</p>
            </div>
            @if($currentKpi)
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $currentKpi->status_badge_class }}">
                    {{ $currentKpi->status_label }}
                </span>
            @endif
        </div>

        @if($currentKpi)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <!-- Hours Target -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between text-xs font-bold mb-2">
                        <span class="text-slate-600">Thời lượng Stream:</span>
                        <span class="text-slate-900">{{ $currentKpi->achieved_hours }}h / {{ $currentKpi->target_hours }}h</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ $currentKpi->hours_progress_percentage }}%"></div>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-2 block font-medium">Hoàn thành {{ $currentKpi->hours_progress_percentage }}% mục tiêu giờ</span>
                </div>

                <!-- Revenue Target -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex justify-between text-xs font-bold mb-2">
                        <span class="text-slate-600">Doanh thu Booking:</span>
                        <span class="text-emerald-600">{{ $currentKpi->formatted_achieved_revenue }} / {{ $currentKpi->formatted_target_revenue }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-indigo-500 h-3 rounded-full transition-all duration-500" style="width: {{ $currentKpi->revenue_progress_percentage }}%"></div>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-2 block font-medium">Hoàn thành {{ $currentKpi->revenue_progress_percentage }}% chỉ tiêu doanh số</span>
                </div>
            </div>
        @else
            <div class="text-center py-6 text-slate-400 text-xs">
                Chưa có mục tiêu KPI cho tháng này. Manager sẽ sớm thiết lập chỉ tiêu cho bạn.
            </div>
        @endif
    </div>

    <!-- 2 Column Section: Upcoming Bookings & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Bookings -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center space-x-2">
                <i class="fas fa-handshake text-emerald-600"></i>
                <span>Hợp Đồng Booking Sắp Diễn Ra</span>
            </h3>

            @if($upcomingBookings->isEmpty())
                <div class="flex-1 flex items-center justify-center p-8 text-slate-400 text-xs">
                    Chưa có lịch booking sắp tới.
                </div>
            @else
                <div class="space-y-3 flex-1">
                    @foreach($upcomingBookings as $ub)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition text-xs">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-bold text-slate-900">{{ $ub->client->name }}</span>
                                <span class="font-black text-emerald-600">{{ number_format($ub->streamer_net_income, 0, ',', '.') }} đ</span>
                            </div>
                            <p class="text-slate-500 line-clamp-1 mb-2">{{ $ub->job_description }}</p>
                            <div class="flex justify-between items-center text-[11px] text-slate-400 border-t border-slate-100 pt-2">
                                <span><i class="far fa-clock mr-1"></i> {{ $ub->start_time->format('H:i d/m') }} - {{ $ub->end_time->format('H:i d/m/Y') }}</span>
                                <span class="px-2 py-0.5 rounded font-bold {{ $ub->status_badge_class }}">{{ $ub->status_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Performance Chart -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center space-x-2">
                <i class="fas fa-chart-line text-emerald-600"></i>
                <span>Lượng Người Xem Livestream Gần Đây</span>
            </h3>
            <div class="h-64">
                <canvas id="streamerTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('streamerTrendChart');
    if (!ctx) return;

    const rawDates = {!! json_encode($chartMetrics->pluck('stream_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!};
    const rawAvg = {!! json_encode($chartMetrics->pluck('avg_viewers')) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: rawDates.length > 0 ? rawDates : ['Ngày 1', 'Ngày 2', 'Ngày 3', 'Ngày 4', 'Ngày 5'],
            datasets: [{
                label: 'Avg Viewers',
                data: rawAvg.length > 0 ? rawAvg : [10000, 11500, 12000, 14500, 13800],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });
});
</script>
@endpush
