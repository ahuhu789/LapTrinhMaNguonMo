@extends('layouts.admin')

@section('title', 'Quản Lý Tài Khoản - MCN Platform')
@section('page_title', 'Quản Lý Tài Khoản Toàn Hệ Thống')

@section('content')
<div class="space-y-6">
    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc email..."
                class="text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-primary-500 w-64">
            
            <select name="role" class="text-xs border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">-- Tất cả vai trò --</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="streamer" {{ request('role') == 'streamer' ? 'selected' : '' }}>Streamer</option>
                <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                Tìm kiếm
            </button>
        </form>
    </div>

    @php
        // Backend (UpdateUserRoleRequest) reports errors under 'role' without saying which row.
        // Each form posts a hidden _target_user_id, which Laravel flashes back as old input,
        // so the message can be shown next to the right row.
        $roleErrorUserId = $errors->has('role') ? (int) old('_target_user_id') : null;
        $roleErrorRowVisible = $roleErrorUserId && $users->contains('id', $roleErrorUserId);
    @endphp

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($errors->has('role') && !$roleErrorRowVisible)
            <div class="m-4 mb-0 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center" role="alert">
                <i class="fas fa-circle-exclamation mr-2"></i>
                <span>Không thể cập nhật vai trò: {{ $errors->first('role') }}</span>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Mã ID</th>
                        <th class="py-3 px-4">Họ và Tên</th>
                        <th class="py-3 px-4">Địa Chỉ Email</th>
                        <th class="py-3 px-4">Vai Trò Hiện Tại</th>
                        <th class="py-3 px-4">Ngày Đăng Ký</th>
                        <th class="py-3 px-4 text-right">Phân Quyền Lại</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">#{{ $u->id }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $u->name }}</td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $u->email }}</td>
                            <td class="py-3.5 px-4">
                                @if($u->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">ADMIN</span>
                                @elseif($u->role === 'manager')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">MANAGER</span>
                                @elseif($u->role === 'streamer')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">STREAMER</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">CLIENT</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-400"><x-datetime :value="$u->created_at" format="d/m/Y" /></td>
                            <td class="py-3.5 px-4 text-right">
                                @php $roleErrorHere = $roleErrorRowVisible && $roleErrorUserId === $u->id; @endphp
                                <form action="{{ route('admin.users.role', $u->id) }}" method="POST" class="inline-flex items-center space-x-1">
                                    @csrf
                                    <input type="hidden" name="_target_user_id" value="{{ $u->id }}">
                                    <select name="role" @if($roleErrorHere) aria-invalid="true" @endif class="text-[11px] border {{ $roleErrorHere ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-300' }} rounded-lg px-2 py-1 focus:ring-1 focus:ring-primary-500">
                                        <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="manager" {{ $u->role == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="streamer" {{ $u->role == 'streamer' ? 'selected' : '' }}>Streamer</option>
                                        <option value="client" {{ $u->role == 'client' ? 'selected' : '' }}>Client</option>
                                    </select>
                                    <button type="submit" class="px-2 py-1 bg-slate-800 hover:bg-primary-600 text-white rounded-lg text-[10px] font-bold transition">
                                        Cập nhật
                                    </button>
                                </form>
                                @if($roleErrorHere)
                                    <p class="mt-1 text-[11px] font-semibold text-rose-600" role="alert">{{ $errors->first('role') }}</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
