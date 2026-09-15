@extends('layouts.admin')

@section('title', 'Quản Lý Chỉ Tiêu KPI - MCN Platform')
@section('page_title', 'Quản Lý & Thiết Lập Chỉ Tiêu KPI')

@section('content')
<div class="space-y-6">
    <!-- Top Bar: Filter by Month -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Bảng Theo Dõi Tiến Độ KPI Tháng {{ $selectedMonth }}</h2>
            <p class="text-xs text-slate-500">Hệ thống tự động cộng dồn số giờ stream khi Manager upload file CSV số liệu.</p>
        </div>

        <form action="{{ route('manager.kpis') }}" method="GET" class="flex items-center space-x-2">
            <input type="month" name="month" value="{{ $selectedMonth }}" 
                class="text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-primary-500 font-bold text-slate-700">
            <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                Xem Tháng
            </button>
        </form>
    </div>

    @if(Auth::user()->isManager() || Auth::user()->isAdmin())
        <!-- Form Set KPI -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center space-x-2">
                <i class="fas fa-bullseye text-primary-600"></i>
                <span>Thiết Lập / Cập Nhật Mục Tiêu KPI Mới</span>
            </h3>

            <form action="{{ route('manager.kpis.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Chọn Streamer</label>
                    <select name="streamer_id" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Chọn streamer --</option>
                        @foreach($streamers as $st)
                            <option value="{{ $st->id }}">{{ $st->stage_name }} ({{ $st->category }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tháng Áp Dụng</label>
                    <input type="month" name="month_year" value="{{ $selectedMonth }}" required
                        class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Chỉ tiêu giờ (Giờ)</label>
                    <input type="number" name="target_hours" value="60" min="1" step="0.5" required
                        class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Doanh thu mục tiêu (VNĐ)</label>
                    <input type="number" name="target_revenue" value="20000000" min="0" step="1000000" required
                        class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500 font-bold">
                </div>

                <div class="flex items-end">
                    <input type="hidden" name="target_avg_viewers" value="1000">
                    <button type="submit" class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                        Lưu Chỉ Tiêu KPI
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- KPI Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="font-bold text-slate-900 text-sm">Danh Sách Tiến Độ KPI Tháng {{ $selectedMonth }}</h3>
        </div>

        @if($kpis->isEmpty())
            <div class="p-12 text-center text-slate-400 text-xs">
                <i class="fas fa-bullseye text-3xl mb-2"></i>
                <p>Chưa có mục tiêu KPI nào được thiết lập trong tháng này.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Talent Streamer</th>
                            <th class="py-3 px-4">Giờ Live Đạt Được</th>
                            <th class="py-3 px-4">Tiến Độ Giờ Live</th>
                            <th class="py-3 px-4">Doanh Thu Đạt Được</th>
                            <th class="py-3 px-4">Tiến Độ Doanh Thu</th>
                            <th class="py-3 px-4">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($kpis as $kpi)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $kpi->streamer->stage_name }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $kpi->streamer->category }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800">{{ $kpi->achieved_hours }}h</span> / {{ $kpi->target_hours }}h
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $kpi->hours_progress_percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $kpi->hours_progress_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-emerald-600">{{ $kpi->formatted_achieved_revenue }}</span>
                                    <span class="text-[10px] text-slate-400 block">Mục tiêu: {{ $kpi->formatted_target_revenue }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $kpi->revenue_progress_percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $kpi->revenue_progress_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full font-bold border text-[10px] {{ $kpi->status_badge_class }}">
                                        {{ $kpi->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $kpis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
