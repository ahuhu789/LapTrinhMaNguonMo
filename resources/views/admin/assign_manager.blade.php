@extends('layouts.admin')

@section('title', 'Phân Bổ Streamer Cho Manager - MCN Platform')
@section('page_title', 'Phân Bổ Streamer Cho Manager Phụ Trách')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-1">Ma Trận Phân Bổ Quản Lý (Talent Assignment)</h3>
        <p class="text-xs text-slate-500 mb-6">
            Mỗi Talent Streamer sẽ do một Manager trực tiếp chịu trách nhiệm duyệt hợp đồng booking và quản lý mục tiêu KPI hàng tháng.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Talent Streamer</th>
                        <th class="py-3 px-4">Thể Loại</th>
                        <th class="py-3 px-4">Giá Thuê / Giờ</th>
                        <th class="py-3 px-4">Manager Hiện Tại</th>
                        <th class="py-3 px-4 text-right">Chỉ Định Manager Mới</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($streamers as $st)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center space-x-2">
                                <img src="{{ $st->avatar_url ?? 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=100' }}" class="w-7 h-7 rounded-full object-cover">
                                <span>{{ $st->stage_name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $st->category }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $st->formatted_rate }}</td>
                            <td class="py-3.5 px-4">
                                @if($st->manager)
                                    <span class="font-bold text-slate-800">{{ $st->manager->name }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $st->manager->email }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 font-bold">Chưa gán</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('admin.assign.update', $st->id) }}" method="POST" class="inline-flex items-center space-x-2">
                                    @csrf
                                    <select name="manager_id" class="text-xs border border-slate-300 rounded-xl px-3 py-1.5 focus:ring-1 focus:ring-primary-500 font-medium">
                                        <option value="">-- Chưa gán --</option>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}" {{ $st->manager_id == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                        Lưu Phân Bổ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
