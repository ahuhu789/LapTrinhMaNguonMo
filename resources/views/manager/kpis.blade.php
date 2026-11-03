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
                <span>Thiết lập mục tiêu</span>
            </h3>

            <p class="text-xs text-slate-500 mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3">
                <i class="fas fa-circle-info text-primary-500 mr-1"></i>
                Giờ live tính từ số liệu live; doanh thu = tổng booking đã hoàn thành trong tháng; Hoàn thành khi cả 2 đạt 100%.
            </p>

            <form action="{{ route('manager.kpis.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label for="kpi_streamer_id" class="block text-xs font-semibold text-slate-600 mb-1">Streamer</label>
                    <select name="streamer_id" id="kpi_streamer_id" required class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Chọn streamer --</option>
                        @foreach($streamers as $st)
                            <option value="{{ $st->id }}">{{ $st->stage_name }} ({{ $st->category }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kpi_month_year" class="block text-xs font-semibold text-slate-600 mb-1">Tháng</label>
                    <input type="month" name="month_year" id="kpi_month_year" value="{{ $selectedMonth }}" required
                        class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label for="kpi_target_hours" class="block text-xs font-semibold text-slate-600 mb-1">Chỉ tiêu giờ live/tháng (giờ)</label>
                    <input type="number" name="target_hours" id="kpi_target_hours" value="60" min="1" step="0.5" required
                        class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label for="kpi_target_revenue" class="block text-xs font-semibold text-slate-600 mb-1">Doanh thu mục tiêu/tháng (đ)</label>
                    <input type="number" name="target_revenue" id="kpi_target_revenue" value="20000000" min="0" required
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
                            @php
                                // Real % (not capped) for the label; bar width capped at 100%.
                                $__hPct = (float) $kpi->target_hours > 0 ? round((float) $kpi->achieved_hours / (float) $kpi->target_hours * 100) : 0;
                                $__rPct = (float) $kpi->target_revenue > 0 ? round((float) $kpi->achieved_revenue / (float) $kpi->target_revenue * 100) : 0;
                            @endphp
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
                                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ min(100, max(0, $__hPct)) }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ (int) $__hPct }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-emerald-600"><x-money :amount="$kpi->achieved_revenue" /></span>
                                    <span class="text-[10px] text-slate-400 block">Mục tiêu: <x-money :amount="$kpi->target_revenue" /></span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min(100, max(0, $__rPct)) }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ (int) $__rPct }}%</span>
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
