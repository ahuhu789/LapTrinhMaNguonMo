@extends('layouts.app')

@section('title', 'MCN Talent Platform - Hệ thống Quản lý và Booking Streamers Hàng Đầu')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-slate-900 via-primary-950 to-indigo-950 text-white py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#a855f7_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-semibold text-primary-200 border border-white/10 mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Nền tảng Quản trị & Marketplace Booking Streamer B2B</span>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight mb-6">
                Kết Nối Nhãn Hàng Với <span class="bg-gradient-to-r from-primary-400 via-indigo-300 to-pink-400 bg-clip-text text-transparent">Top Talent Streamers</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 mb-8 font-light leading-relaxed">
                Hệ thống quản lý Agency toàn diện: Hồ sơ Talent Press Kit chuẩn xác, thuật toán khóa lịch không trùng lặp, theo dõi KPI tự động và trợ lý AI tư vấn thông minh.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('talents.index') }}" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white font-bold shadow-lg shadow-primary-600/30 transition flex items-center space-x-2">
                    <i class="fas fa-magnifying-glass"></i>
                    <span>Khám Phá Talent</span>
                </a>
                <a href="{{ route('bookings.create') }}" class="px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold backdrop-blur-md border border-white/20 transition flex items-center space-x-2">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Đặt Lịch Booking Ngay</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Counter -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center md:border-r border-slate-100">
            <div class="text-3xl sm:text-4xl font-black text-primary-600">{{ $stats['total_talents'] }}+</div>
            <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1 uppercase">Talent Độc Quyền</div>
        </div>
        <div class="text-center md:border-r border-slate-100">
            <div class="text-3xl sm:text-4xl font-black text-indigo-600">{{ number_format($stats['total_hours']) }}h</div>
            <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1 uppercase">Giờ Livestream</div>
        </div>
        <div class="text-center md:border-r border-slate-100">
            <div class="text-3xl sm:text-4xl font-black text-emerald-600">{{ $stats['total_bookings'] }}</div>
            <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1 uppercase">Chiến Dịch Hoàn Thành</div>
        </div>
        <div class="text-center">
            <div class="text-3xl sm:text-4xl font-black text-amber-500">{{ number_format($stats['total_views'] / 1000, 1) }}k+</div>
            <div class="text-xs sm:text-sm font-semibold text-slate-500 mt-1 uppercase">Lượt Tiếp Cận Khán Giả</div>
        </div>
    </div>
</section>

<!-- Featured Talents -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10">
        <div>
            <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">Gương Mặt Nổi Bật</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-1">Top Talent Được Săn Đón Nhất</h2>
        </div>
        <a href="{{ route('talents.index') }}" class="text-primary-600 font-bold hover:text-primary-700 text-sm flex items-center space-x-1 mt-3 sm:mt-0">
            <span>Xem tất cả Talent</span>
            <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($featuredTalents as $talent)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200 overflow-hidden flex flex-col group">
                <!-- Banner & Avatar -->
                <div class="h-32 bg-slate-200 relative overflow-hidden">
                    <img src="{{ $talent->banner_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800' }}" alt="Banner" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-xs font-bold bg-white/90 text-slate-800 backdrop-blur-sm shadow-sm">
                        {{ $talent->category }}
                    </span>
                </div>
                
                <div class="px-6 pt-0 pb-6 flex-1 flex flex-col relative">
                    <div class="flex justify-between items-end -mt-10 mb-3">
                        <img src="{{ $talent->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200' }}" alt="{{ $talent->stage_name }}" class="w-20 h-20 rounded-2xl border-4 border-white shadow-md object-cover">
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block font-medium">Giá tham khảo</span>
                            <span class="text-base font-extrabold text-primary-600">{{ $talent->formatted_rate }}</span>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-1.5">
                        <span>{{ $talent->stage_name }}</span>
                        <i class="fas fa-circle-check text-blue-500 text-xs" title="Verified Talent"></i>
                    </h3>
                    
                    <p class="text-xs text-slate-500 line-clamp-2 mt-1 mb-4 flex-1">
                        {{ $talent->bio }}
                    </p>

                    <!-- Metrics summary -->
                    <div class="bg-slate-50 rounded-xl p-3 grid grid-cols-2 gap-2 text-center text-xs mb-4 border border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Avg Viewers</span>
                            <span class="font-bold text-slate-800">{{ number_format($talent->avg_viewers) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Peak Viewers</span>
                            <span class="font-bold text-indigo-600">{{ number_format($talent->peak_viewers) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('talents.show', $talent->id) }}" class="text-center py-2 px-3 rounded-xl border border-slate-300 hover:border-primary-500 hover:text-primary-600 font-semibold text-xs transition">
                            Xem Media Kit
                        </a>
                        <a href="{{ route('bookings.create', ['streamer_id' => $talent->id]) }}" class="text-center py-2 px-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs transition shadow-sm">
                            Đặt Lịch
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Workflow Feature Section -->
<section class="bg-slate-100 py-16 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">Quy Trình Chuẩn Hóa</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-1">Booking Minh Bạch & Chuyên Nghiệp</h2>
            <p class="text-sm text-slate-500 mt-2">Loại bỏ rủi ro trùng lịch và tối ưu hóa hiệu quả chiến dịch với hệ thống 4 bước chuẩn mực.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative">
                <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xl mb-4">1</div>
                <h4 class="font-bold text-base mb-2">Chọn Talent & Gửi Lịch</h4>
                <p class="text-xs text-slate-500">Khách hàng xem Media Kit, chọn khung giờ trống và gửi kịch bản chiến dịch.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl mb-4">2</div>
                <h4 class="font-bold text-base mb-2">Kiểm Tra Xung Đột Lịch</h4>
                <p class="text-xs text-slate-500">Thuật toán Collision-Free tự động phát hiện và cảnh báo nếu có sự kiện trùng giờ.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xl mb-4">3</div>
                <h4 class="font-bold text-base mb-2">Manager Phê Duyệt</h4>
                <p class="text-xs text-slate-500">Quản lý duyệt hợp đồng, hệ thống tự động khóa lịch trình trên lịch tổng hợp.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl mb-4">4</div>
                <h4 class="font-bold text-base mb-2">Tự Động Đo Lường KPI</h4>
                <p class="text-xs text-slate-500">Import file CSV số liệu buổi stream, doanh thu và giờ stream tự động cộng dồn KPI.</p>
            </div>
        </div>
    </div>
</section>
@endsection
