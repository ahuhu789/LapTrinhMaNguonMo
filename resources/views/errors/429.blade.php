{{-- 429 Too Many Requests: shows Retry-After (seconds or HTTP date) when the exception carries it. --}}
@php
    $retryAfter = null;
    if (isset($exception) && method_exists($exception, 'getHeaders')) {
        $headers = $exception->getHeaders();
        $raw = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
        if (is_numeric($raw)) {
            $retryAfter = max(1, (int) $raw);
        } elseif (is_string($raw) && ($ts = strtotime($raw)) !== false) {
            $retryAfter = max(1, $ts - time());
        }
    }
    $retryText = null;
    if ($retryAfter !== null) {
        $retryText = $retryAfter < 60 ? $retryAfter . ' giây' : (int) ceil($retryAfter / 60) . ' phút';
    }
@endphp
@extends('errors.layout')
@section('title', 'Quá nhiều yêu cầu (429)')
@section('code', '429')
@section('icon', 'fa-hourglass-half')
@section('icon_bg', 'bg-amber-100 text-amber-600')
@section('code_color', 'text-amber-600')
@section('heading', 'Bạn thao tác quá nhanh')
@section('message')
    Hệ thống tạm thời giới hạn số lượng yêu cầu để bảo vệ dịch vụ.
    @if($retryText)
        Vui lòng thử lại sau <strong class="text-slate-800">{{ $retryText }}</strong>.
    @else
        Vui lòng đợi ít phút rồi thử lại.
    @endif
@endsection
