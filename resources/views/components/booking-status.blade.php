{{--
    <x-booking-status :status="$booking->status" />  (size: sm | md | lg)
    Covers every value of bookings.status (migration enum):
    pending, reviewing, approved, rejected, completed, cancelled. Unknown -> raw value, grey.
--}}
@props(['status' => null, 'size' => 'md'])
@php
    $__map = [
        'pending'   => ['Chờ duyệt',          'bg-amber-100 text-amber-800 border-amber-300',      'fa-hourglass-half'],
        'reviewing' => ['Đang xem xét',       'bg-sky-100 text-sky-800 border-sky-300',            'fa-magnifying-glass'],
        'approved'  => ['Đã duyệt & khóa lịch', 'bg-emerald-100 text-emerald-800 border-emerald-300', 'fa-lock'],
        'rejected'  => ['Đã từ chối',         'bg-rose-100 text-rose-800 border-rose-300',         'fa-circle-xmark'],
        'completed' => ['Đã hoàn thành',      'bg-indigo-100 text-indigo-800 border-indigo-300',   'fa-circle-check'],
        'cancelled' => ['Đã hủy',             'bg-slate-100 text-slate-600 border-slate-300',      'fa-ban'],
    ];
    [$__label, $__cls, $__icon] = $__map[$status] ?? [(string) ($status ?? '—'), 'bg-slate-100 text-slate-700 border-slate-300', 'fa-circle'];
    $__size = ['sm' => 'px-2 py-0.5 text-[10px]', 'md' => 'px-2.5 py-1 text-xs', 'lg' => 'px-3 py-1 text-xs'][$size] ?? 'px-2.5 py-1 text-xs';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full font-bold border whitespace-nowrap $__size $__cls"]) }} data-status="{{ $status }}"><i class="fas {{ $__icon }} text-[0.85em]" aria-hidden="true"></i>{{ $__label }}</span>
