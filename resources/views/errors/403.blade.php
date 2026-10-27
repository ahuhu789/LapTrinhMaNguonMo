@extends('errors.layout')
@section('title', 'Không có quyền truy cập (403)')
@section('code', '403')
@section('icon', 'fa-lock')
@section('icon_bg', 'bg-rose-100 text-rose-600')
@section('code_color', 'text-rose-600')
@section('heading', 'Bạn không có quyền truy cập')
@section('message')
    {{-- Messages passed to abort(403, '...') are shown escaped; otherwise a generic text. --}}
    @php $msg = isset($exception) ? trim((string) $exception->getMessage()) : ''; @endphp
    {{ ($msg !== '' && $msg !== 'Forbidden' && $msg !== 'This action is unauthorized.') ? $msg : 'Tài khoản của bạn không được phép xem hoặc thực hiện thao tác này.' }}
@endsection
