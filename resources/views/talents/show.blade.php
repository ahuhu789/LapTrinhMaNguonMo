@extends('layouts.app')

@section('title', $talent->stage_name . ' - Public Talent Media Kit')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Media Kit Banner -->
    <div class="relative bg-slate-900 rounded-3xl overflow-hidden shadow-lg mb-8">
        <div class="h-64 sm:h-80 w-full overflow-hidden relative">
            <img src="{{ $talent->banner_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1600' }}" alt="Banner" class="w-full h-full object-cover opacity-80">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
        </div>

        <div class="relative px-6 sm:px-10 pb-8 -mt-20 flex flex-col sm:flex-row items-center sm:items-end justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-6 text-center sm:text-left">
                <img src="{{ $talent->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=400' }}" alt="{{ $talent->stage_name }}" class="w-32 h-32 rounded-3xl border-4 border-white shadow-2xl object-cover bg-slate-800">
                <div>
                    <div class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-primary-600/90 text-white mb-2 shadow-sm">
                        <span>{{ $talent->category }}</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white flex items-center justify-center sm:justify-start space-x-2">
                        <span>{{ $talent->stage_name }}</span>
                        <i class="fas fa-circle-check text-blue-400 text-lg" title="Verified Creator"></i>
                    </h1>
                    <p class="text-slate-300 text-sm mt-1 max-w-xl">
                        {{ $talent->bio }}
                    </p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-2xl text-center sm:text-right w-full sm:w-auto">
                <span class="text-xs text-slate-300 uppercase font-semibold block">Mức giá Booking chuẩn</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 block my-1">{{ $talent->formatted_rate }}</span>
                <a href="{{ route('bookings.create', ['streamer_id' => $talent->id]) }}" class="mt-2 inline-block w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-primary-600/30 transition">
                    <i class="fas fa-calendar-check mr-1.5"></i> Đặt Lịch Booking Ngay
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Key Engagement Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Avg Viewers</span>
                <div class="text-2xl font-black text-slate-900">{{ number_format($talent->avg_viewers) }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold"><i class="fas fa-arrow-trend-up mr-1"></i> Ổn định</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-fire"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Peak Viewers</span>
                <div class="text-2xl font-black text-slate-900">{{ number_format($talent->peak_viewers) }}</div>
                <span class="text-[11px] text-indigo-600 font-semibold">Kỷ lục cao nhất</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Giờ Live Tháng Này</span>
                <div class="text-2xl font-black text-slate-900">{{ number_format($talent->monthly_stream_hours, 1) }}h</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Chỉ số cam kết</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-tag"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Giá Booking / Giờ</span>
                <div class="text-xl font-black text-slate-900">{{ number_format($talent->rate_per_hour, 0, ',', '.') }} đ</div>
                <span class="text-[11px] text-slate-500">Chưa bao gồm VAT</span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Cols: Platform Breakdown & Viewer Growth Chart -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Platform Breakdown -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center space-x-2">
                    <i class="fas fa-chart-pie text-primary-600"></i>
                    <span>Hiệu Suất Theo Nền Tảng Phát Sóng</span>
                </h3>
                
                @if($platformBreakdown->isEmpty())
                    <p class="text-sm text-slate-500 py-4">Chưa có số liệu phát sóng được ghi nhận.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-bold">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-xl">Nền Tảng</th>
                                    <th class="py-3 px-4">Số Buổi Stream</th>
                                    <th class="py-3 px-4">Tổng Giờ Live</th>
                                    <th class="py-3 px-4">Avg Viewers</th>
                                    <th class="py-3 px-4 rounded-r-xl">Peak Viewers</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($platformBreakdown as $pb)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 px-4 font-semibold text-slate-800 capitalize flex items-center space-x-2">
                                            @if($pb->platform == 'youtube')
                                                <i class="fab fa-youtube text-red-600 text-base"></i>
                                            @elseif($pb->platform == 'twitch')
                                                <i class="fab fa-twitch text-purple-600 text-base"></i>
                                            @elseif($pb->platform == 'tiktok')
                                                <i class="fab fa-tiktok text-slate-900 text-base"></i>
                                            @elseif($pb->platform == 'facebook')
                                                <i class="fab fa-facebook text-blue-600 text-base"></i>
                                            @endif
                                            <span>{{ $pb->platform }}</span>
                                        </td>
                                        <td class="py-3 px-4">{{ $pb->sessions }} buổi</td>
                                        <td class="py-3 px-4 font-medium">{{ number_format($pb->total_hours, 1) }} giờ</td>
                                        <td class="py-3 px-4 font-bold text-slate-800">{{ number_format($pb->avg_viewers) }}</td>
                                        <td class="py-3 px-4 font-bold text-indigo-600">{{ number_format($pb->peak_viewers) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Viewer Growth Trend Chart -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center space-x-2">
                    <i class="fas fa-chart-line text-indigo-600"></i>
                    <span>Biểu Đồ Lượng Viewers Các Buổi Gần Nhất</span>
                </h3>
                <div class="h-64">
                    <canvas id="viewerTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Upcoming Schedule & Booking Contact -->
        <div class="space-y-8">
            <!-- 7-Day Schedule Preview -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-2 flex items-center space-x-2">
                    <i class="fas fa-calendar-week text-primary-600"></i>
                    <span>Lịch Trình 7 Ngày Tới</span>
                </h3>
                <p class="text-xs text-slate-500 mb-4">Các khung giờ streamer đã có lịch để bạn tránh bị trùng lặp khi đặt.</p>

                @if($upcomingSchedules->isEmpty())
                    <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-4 text-xs">
                        <i class="fas fa-circle-check mr-1.5 text-emerald-600"></i> Streamer hiện đang hoàn toàn trống lịch trong 7 ngày tới!
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingSchedules as $sch)
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-bold text-slate-800">{{ $sch->title }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sch->event_badge_class }}">
                                        {{ $sch->event_type_label }}
                                    </span>
                                </div>
                                <div class="text-slate-500 flex items-center space-x-2">
                                    <i class="far fa-clock"></i>
                                    <span>{{ $sch->start_time->format('H:i d/m') }} - {{ $sch->end_time->format('H:i d/m/Y') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Agency Guarantee Card -->
            <div class="bg-gradient-to-br from-primary-900 to-indigo-950 rounded-2xl p-6 text-white shadow-md">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-shield-halved text-primary-400"></i>
                </div>
                <h4 class="font-bold text-base mb-2">Cam Kết Từ MCN Agency</h4>
                <ul class="text-xs text-slate-300 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-400 mr-2 mt-0.5"></i>
                        <span>Bảo đảm đúng giờ và đủ thời lượng cam kết trong hợp đồng.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-400 mr-2 mt-0.5"></i>
                        <span>Kiểm duyệt kịch bản truyền thông kỹ lưỡng trước buổi live.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-400 mr-2 mt-0.5"></i>
                        <span>Cung cấp báo cáo số liệu CSV sau buổi live trong vòng 24h.</span>
                    </li>
                </ul>

                <a href="{{ route('bookings.create', ['streamer_id' => $talent->id]) }}" class="mt-5 block text-center w-full py-3 bg-white text-primary-900 font-black rounded-xl hover:bg-primary-50 transition shadow-sm text-sm">
                    Gửi Yêu Cầu Booking
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('viewerTrendChart');
    if (!ctx) return;

    const rawDates = {!! json_encode($recentMetrics->pluck('stream_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!};
    const rawAvg = {!! json_encode($recentMetrics->pluck('avg_viewers')) !!};
    const rawPeak = {!! json_encode($recentMetrics->pluck('peak_viewers')) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: rawDates.length > 0 ? rawDates : ['Buổi 1', 'Buổi 2', 'Buổi 3', 'Buổi 4', 'Buổi 5'],
            datasets: [
                {
                    label: 'Peak Viewers',
                    data: rawPeak.length > 0 ? rawPeak : [15000, 18000, 16500, 22000, 19500],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                },
                {
                    label: 'Average Viewers',
                    data: rawAvg.length > 0 ? rawAvg : [11000, 12500, 11800, 14000, 13200],
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
});
</script>
@endpush
