{{--
    KPI status badge. "failed" is always shown as "Không đạt" (overrides model "Chưa đạt").
    <x-kpi-status :kpi="$kpi" />
--}}
@props(['kpi' => null, 'status' => null, 'size' => 'md'])
@php
    $st = $status ?? $kpi?->status;
    $text = $kpi?->status_label ?? (string) ($st ?? '—');
    if ($st === 'failed' || $text === 'Chưa đạt') {
        $text = 'Không đạt';
    }
    $cls = $kpi?->status_badge_class ?? 'bg-gray-100 text-gray-800 border-gray-300';
    $sizeCls = ['sm' => 'px-2 py-0.5 text-[10px]', 'md' => 'px-2.5 py-1 text-xs', 'lg' => 'px-3 py-1 text-xs'][$size] ?? 'px-2.5 py-1 text-xs';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-bold border whitespace-nowrap $sizeCls $cls"]) }} data-status="{{ $st }}">{{ $text }}</span>
