{{--
    "Đặt booking" link shown only to clients. Guests are sent to login (then back to the form);
    logged-in non-client roles (streamer/manager/admin) see nothing.
    <x-booking-link :streamer="$talent->id" class="...">Đặt Lịch</x-booking-link>
--}}
@props(['streamer' => null])
@php
    $__target = route('bookings.create', $streamer ? ['streamer_id' => $streamer] : []);
@endphp
@guest
    <a href="{{ route('login') }}" data-booking-link="guest" title="Đăng nhập bằng tài khoản Client để đặt booking" {{ $attributes }}>{{ $slot }}</a>
@else
    @if(auth()->user()->isClient())
        <a href="{{ $__target }}" data-booking-link="client" {{ $attributes }}>{{ $slot }}</a>
    @endif
@endguest
