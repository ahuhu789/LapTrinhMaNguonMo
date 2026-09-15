@extends('layouts.streamer')

@section('title', 'Lịch Trình Cá Nhân - ' . $profile->stage_name)
@section('page_title', 'Lịch Trình & Thời Gian Biểu')

@section('content')
<div class="space-y-6">
    <!-- Quick Add Event Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center space-x-2">
            <i class="fas fa-calendar-plus text-emerald-600"></i>
            <span>Đăng Ký Lịch Phát Sóng Hoặc Lịch Bận Cá Nhân</span>
        </h3>

        <form action="{{ route('streamer.schedule.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Loại Sự Kiện</label>
                <select name="event_type" required class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
                    <option value="stream">Lịch Live Stream</option>
                    <option value="personal">Lịch Bận Cá Nhân / Nghỉ</option>
                    <option value="training">Lịch Training / Họp Team</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Thời Gian Bắt Đầu</label>
                <input type="datetime-local" name="start_time" required
                    class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Thời Gian Kết Thúc</label>
                <input type="datetime-local" name="end_time" required
                    class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Tiêu Đề Lịch Trình</label>
                <input type="text" name="title" placeholder="VD: Live rank CS2 đêm" required
                    class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition">
                    Thêm Vào Lịch Trình
                </button>
            </div>
        </form>
    </div>

    <!-- Schedule List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 text-sm">Thời Gian Biểu Đã Ghi Nhận</h3>
            <span class="text-xs text-slate-500">Hệ thống tự khóa lịch khi có booking được duyệt</span>
        </div>

        @if($schedules->isEmpty())
            <div class="p-12 text-center text-slate-400 text-xs">
                Chưa có sự kiện nào trong lịch trình.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Loại Sự Kiện</th>
                            <th class="py-3 px-4">Tiêu Đề / Nội Dung</th>
                            <th class="py-3 px-4">Bắt Đầu</th>
                            <th class="py-3 px-4">Kết Thúc</th>
                            <th class="py-3 px-4 text-right">Chi Tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($schedules as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $s->event_badge_class }}">
                                        {{ $s->event_type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $s->title }}</td>
                                <td class="py-3 px-4 text-slate-700 font-medium">{{ $s->start_time->format('H:i d/m/Y') }}</td>
                                <td class="py-3 px-4 text-slate-700 font-medium">{{ $s->end_time->format('H:i d/m/Y') }}</td>
                                <td class="py-3 px-4 text-right">
                                    @if($s->reference_id)
                                        <a href="{{ route('bookings.show', $s->reference_id) }}" class="text-primary-600 font-bold hover:underline">
                                            Hợp đồng #{{ $s->reference_id }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
