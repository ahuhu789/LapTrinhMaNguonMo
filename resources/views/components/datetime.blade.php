{{--
    <x-datetime :value="$booking->start_time" />            -> 29/10/2026 20:00
    <x-datetime :value="$u->created_at" format="d/m/Y" />   -> 27/10/2026
    Accepts Carbon/DateTimeInterface, date strings and unix timestamps; null/invalid -> $empty.
    Always rendered in Asia/Ho_Chi_Minh.
--}}
@props(['value' => null, 'format' => 'd/m/Y H:i', 'empty' => '—', 'tz' => 'Asia/Ho_Chi_Minh'])
@php
    $__dt = null;
    try {
        if ($value instanceof \DateTimeInterface) {
            $__dt = \Illuminate\Support\Carbon::instance($value);
        } elseif (is_numeric($value)) {
            $__dt = \Illuminate\Support\Carbon::createFromTimestamp((int) $value);
        } elseif (is_string($value) && trim($value) !== '') {
            $__dt = \Illuminate\Support\Carbon::parse($value);
        }
    } catch (\Throwable $e) {
        $__dt = null;
    }
    $__dt = $__dt?->copy()->timezone($tz);
@endphp
@if($__dt)<time datetime="{{ $__dt->toIso8601String() }}" {{ $attributes }}>{{ $__dt->format($format) }}</time>@else<span {{ $attributes }}>{{ $empty }}</span>@endif
