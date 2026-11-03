{{--
    <x-money :amount="$booking->budget" />                       -> 15.000.000đ
    <x-money :amount="$talent->rate_per_hour" suffix="/giờ" />    -> 5.000.000đ/giờ
    <x-money :amount="$x" sign="-" />                             -> -2.250.000đ
    Non-numeric/null -> $empty.
--}}
@props(['amount' => null, 'suffix' => '', 'sign' => '', 'empty' => '—'])
@if(is_numeric($amount))<span {{ $attributes->merge(['class' => 'whitespace-nowrap']) }}>{{ $sign }}{{ number_format((float) $amount, 0, ',', '.') }}đ{{ $suffix }}</span>@else<span {{ $attributes }}>{{ $empty }}</span>@endif
