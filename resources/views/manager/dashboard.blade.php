@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển Manager - MCN Platform')
@section('page_title', 'Bảng Điều Khiển Quản Lý (Manager Workspace)')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Talent Phụ Trách</span>
                <div class="text-2xl font-black text-slate-900">{{ $managedStreamers->count() }} Streamers</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Booking Chờ Duyệt</span>
                <div class="text-2xl font-black text-amber-600">{{ $pendingBookings->count() }} đơn</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase">Doanh Số Tháng Này</span>
                <div class="text-2xl font-black text-emerald-600">{{ number_format($teamRevenue, 0, ',', '.') }} đ</div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings Attention Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                <span>Yêu Cầu Booking Đang Chờ Phê Duyệt</span>
            </h3>
            <span class="text-xs text-slate-400">Yêu cầu phản hồi trong 24h</span>
        </div>

        @if($pendingBookings->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs font-medium">
                <i class="fas fa-check-circle text-2xl text-emerald-500 mb-2"></i>
                <p>Hiện không có yêu cầu booking nào đang chờ duyệt.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="py-2.5 px-4">Mã #</th>
                            <th class="py-2.5 px-4">Khách Hàng</th>
                            <th class="py-2.5 px-4">Talent Streamer</th>
                            <th class="py-2.5 px-4">Thời Gian</th>
                            <th class="py-2.5 px-4">Ngân Sách</th>
                            <th class="py-2.5 px-4 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingBookings as $pb)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-mono font-bold">#{{ $pb->id }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $pb->client->name }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $pb->streamer->stage_name }}</td>
                                <td class="py-3 px-4">{{ $pb->start_time->format('H:i d/m') }} - {{ $pb->end_time->format('H:i d/m/Y') }}</td>
                                <td class="py-3 px-4 font-extrabold text-emerald-600">{{ $pb->formatted_budget }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('bookings.show', $pb->id) }}" class="px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold transition">
                                        Xem & Phê Duyệt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Managed Streamers List & KPI Progress -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-sm">Danh Sách Talent Thuộc Nhóm Quản Lý</h3>
            <a href="{{ route('manager.kpis') }}" class="text-xs text-primary-600 font-bold hover:underline">
                Thiết lập chỉ tiêu KPI <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="py-2.5 px-4">Talent</th>
                        <th class="py-2.5 px-4">Thể Loại</th>
                        <th class="py-2.5 px-4">Giá Thuê / Giờ</th>
                        <th class="py-2.5 px-4">Tiến Độ Giờ Live KPI</th>
                        <th class="py-2.5 px-4">Tiến Độ Doanh Thu KPI</th>
                        <th class="py-2.5 px-4 text-right">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($managedStreamers as $st)
                        @php $kpi = $st->currentKpi; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-bold text-slate-900 flex items-center space-x-2">
                                <img src="{{ $st->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=100' }}" class="w-7 h-7 rounded-full object-cover">
                                <span>{{ $st->stage_name }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $st->category }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $st->formatted_rate }}</td>
                            <td class="py-3 px-4">
                                @if($kpi)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $kpi->hours_progress_percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $kpi->achieved_hours }}/{{ $kpi->target_hours }}h ({{ $kpi->hours_progress_percentage }}%)</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Chưa đặt KPI</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($kpi)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $kpi->revenue_progress_percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $kpi->revenue_progress_percentage }}%</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('talents.show', $st->id) }}" target="_blank" class="text-primary-600 hover:underline font-semibold mr-2">
                                    Media Kit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
