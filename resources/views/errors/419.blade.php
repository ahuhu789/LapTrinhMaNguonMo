@extends('errors.layout')
@section('title', 'Phiên làm việc hết hạn (419)')
@section('code', '419')
@section('icon', 'fa-clock-rotate-left')
@section('icon_bg', 'bg-amber-100 text-amber-600')
@section('code_color', 'text-amber-600')
@section('heading', 'Phiên làm việc đã hết hạn')
@section('message', 'Vui lòng tải lại trang rồi thực hiện lại thao tác. Nếu vẫn lỗi, hãy đăng nhập lại.')
@section('extra_action')
    <a href="{{ url('/login') }}" class="px-5 py-2.5 rounded-xl border border-violet-300 text-violet-700 hover:bg-violet-50 text-sm font-semibold transition">Đăng nhập</a>
@endsection
