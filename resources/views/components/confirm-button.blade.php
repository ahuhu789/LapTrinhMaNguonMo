{{--
    Danger action trigger. Renders nothing if no action URL and none of $routes exist (Route::has).
    <x-confirm-button :routes="['bookings.cancel']" :params="$booking" title="..." message="..." confirm="Hủy đơn" :reason="true">
        Hủy đơn
    </x-confirm-button>
--}}
@props([
    'action' => null,
    'routes' => [],
    'params' => [],
    'method' => 'POST',
    'title' => 'Xác nhận',
    'message' => 'Bạn có chắc muốn thực hiện thao tác này?',
    'confirm' => 'Xác nhận',
    'reason' => false,
    'reasonName' => 'cancel_reason',
    'reasonLabel' => 'Lý do',
    'reasonPlaceholder' => 'Nhập lý do (tối thiểu 5 ký tự)...',
    'variant' => 'danger',
])
@php
    $url = $action;
    if (!$url) {
        foreach ((array) $routes as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                try {
                    $url = route($name, $params);
                    break;
                } catch (\Throwable $e) {
                    $url = null;
                }
            }
        }
    }
@endphp
@if($url)
<button type="button"
    data-confirm
    data-confirm-action="{{ $url }}"
    data-confirm-method="{{ strtoupper($method) }}"
    data-confirm-title="{{ $title }}"
    data-confirm-message="{{ $message }}"
    data-confirm-label="{{ $confirm }}"
    data-confirm-variant="{{ $variant }}"
    @if($reason)
        data-confirm-reason="1"
        data-confirm-reason-name="{{ $reasonName }}"
        data-confirm-reason-label="{{ $reasonLabel }}"
        data-confirm-reason-placeholder="{{ $reasonPlaceholder }}"
    @endif
    {{ $attributes }}>{{ $slot }}</button>
@endif
