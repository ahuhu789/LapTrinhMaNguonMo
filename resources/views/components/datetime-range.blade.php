{{--
    <x-datetime-range :start="$b->start_time" :end="$b->end_time" />
    same day : 29/10/2026 20:00 – 23:00
    otherwise: 28/10/2026 22:00 – 29/10/2026 02:00
--}}
@props(['start' => null, 'end' => null, 'tz' => 'Asia/Ho_Chi_Minh'])
@php
    $__parse = function ($v) use ($tz) {
        try {
            if ($v instanceof \DateTimeInterface) return \Illuminate\Support\Carbon::instance($v)->copy()->timezone($tz);
            if (is_string($v) && trim($v) !== '') return \Illuminate\Support\Carbon::parse($v)->timezone($tz);
        } catch (\Throwable $e) {}
        return null;
    };
    $__s = $__parse($start);
    $__e = $__parse($end);
@endphp
<span {{ $attributes }}>@if($__s){{ $__s->format('d/m/Y H:i') }}@else—@endif @if($__e)– {{ ($__s && $__s->isSameDay($__e)) ? $__e->format('H:i') : $__e->format('d/m/Y H:i') }}@endif</span>
