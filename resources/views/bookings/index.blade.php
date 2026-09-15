@extends(Auth::user()->isAdmin() || Auth::user()->isManager() ? 'layouts.admin' : (Auth::user()->isStreamer() ? 'layouts.streamer' : 'layouts.app'))

@section('title', 'Quản Lý Hợp Đồng Booking - MCN Platform')
@section('page_title', 'Danh Sách Hợp Đồng Booking')

@section('content')
<div class="{{ Auth::user()->isClient() ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8' : '' }}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Danh Sách Yêu Cầu Booking</h2>
            <p class="text-xs sm:text-sm text-slate-500">Phân quyền hiển thị theo vai trò (RBAC): {{ strtoupper(Auth::user()->role) }}</p>
        </div>
        @if(Auth::user()->isClient() || Auth::user()->isAdmin())
            <a href="{{ route('bookings.create') }}" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-sm transition flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Tạo Booking Mới</span>
            </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($bookings->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <i class="fas fa-calendar-xmark text-4xl mb-3"></i>
                <p class="text-sm font-semibold text-slate-600">Chưa có hợp đồng booking nào.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Mã #</th>
                            <th class="py-3 px-4">Khách Hàng (Client)</th>
                            <th class="py-3 px-4">Talent Streamer</th>
                            <th class="py-3 px-4">Thời Gian Livestream</th>
                            <th class="py-3 px-4">Tổng Ngân Sách</th>
                            <th class="py-3 px-4">Trạng Thái</th>
                            <th class="py-3 px-4 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bookings as $b)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700">#{{ $b->id }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800">{{ $b->client->name }}</div>
                                    <span class="text-[11px] text-slate-400">{{ $b->client->email }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                                        <span>{{ $b->streamer->stage_name }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-normal">
                                            {{ $b->streamer->category }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400">QL: {{ $b->manager->name ?? 'Chưa gán' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="font-semibold text-slate-800">{{ $b->start_time->format('H:i d/m/Y') }}</div>
                                    <div class="text-slate-400">đến {{ $b->end_time->format('H:i d/m/Y') }} ({{ $b->duration_hours }}h)</div>
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                    {{ $b->formatted_budget }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $b->status_badge_class }}">
                                        {{ $b->status_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="{{ route('bookings.show', $b->id) }}" class="inline-block px-3 py-1.5 rounded-lg border border-slate-300 hover:border-primary-500 hover:text-primary-600 font-semibold text-xs transition">
                                        Chi Tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
