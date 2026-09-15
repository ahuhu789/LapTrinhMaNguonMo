@extends('layouts.admin')

@section('title', 'Admin Dashboard - MCN Platform')
@section('page_title', 'Bảng Điều Khiển Quản Trị Hệ Thống (Admin Control Center)')

@section('content')
<div class="space-y-6">
    <!-- 4 High-Level Key Performance Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fas fa-users-viewfinder"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Tổng Thành Viên</span>
                <div class="text-2xl font-black text-slate-900">{{ $stats['total_users'] }}</div>
                <span class="text-[11px] text-slate-500">{{ $stats['total_streamers'] }} Talent • {{ $stats['total_managers'] }} Manager</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-file-contract"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Tổng Số Hợp Đồng</span>
                <div class="text-2xl font-black text-slate-900">{{ $stats['total_bookings'] }}</div>
                <span class="text-[11px] text-amber-600 font-semibold">{{ $stats['pending_bookings'] }} đơn chờ duyệt</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-money-bill-trend-up"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Tổng Doanh Thu Sàn</span>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['total_revenue'], 0, ',', '.') }} đ</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Tất cả chiến dịch</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Hoa Hồng Agency (15%)</span>
                <div class="text-2xl font-black text-indigo-600">{{ number_format($stats['total_commission'], 0, ',', '.') }} đ</div>
                <span class="text-[11px] text-indigo-600 font-semibold">Lợi nhuận gộp MCN</span>
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Recent Bookings & Streamers Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Bookings Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-900 text-sm">Hợp Đồng Booking Mới Nhất</h3>
                <a href="{{ route('bookings.index') }}" class="text-xs text-primary-600 font-bold hover:underline">
                    Xem tất cả <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="py-2.5 px-4">Mã #</th>
                            <th class="py-2.5 px-4">Khách Hàng</th>
                            <th class="py-2.5 px-4">Talent</th>
                            <th class="py-2.5 px-4">Ngân Sách</th>
                            <th class="py-2.5 px-4">Trạng Thái</th>
                            <th class="py-2.5 px-4 text-right">Chi Tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentBookings as $rb)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-mono font-bold">#{{ $rb->id }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $rb->client->name }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $rb->streamer->stage_name }}</td>
                                <td class="py-3 px-4 font-extrabold text-slate-800">{{ $rb->formatted_budget }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full font-bold border text-[10px] {{ $rb->status_badge_class }}">
                                        {{ $rb->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('bookings.show', $rb->id) }}" class="text-primary-600 hover:underline font-bold">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Talent Streamers Quick List (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-900 text-sm">Talents Hệ Thống</h3>
                <a href="{{ route('admin.assign') }}" class="text-xs text-primary-600 font-bold hover:underline">
                    Phân bổ <i class="fas fa-network-wired text-[10px]"></i>
                </a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[420px]">
                @foreach($activeTalents as $at)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2.5">
                            <img src="{{ $at->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=100' }}" class="w-8 h-8 rounded-full object-cover">
                            <div>
                                <div class="font-bold text-slate-900">{{ $at->stage_name }}</div>
                                <span class="text-[10px] text-slate-400">QL: {{ $at->manager->name ?? 'Chưa gán' }}</span>
                            </div>
                        </div>
                        <span class="font-semibold text-primary-600 text-[11px]">{{ $at->formatted_rate }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
