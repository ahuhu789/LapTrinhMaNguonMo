@extends(Auth::user()->isAdmin() || Auth::user()->isManager() ? 'layouts.admin' : (Auth::user()->isStreamer() ? 'layouts.streamer' : 'layouts.app'))

@section('title', 'Hợp Đồng Booking #' . $booking->id . ' - MCN Platform')
@section('page_title', 'Chi Tiết Hợp Đồng Booking #' . $booking->id)

@section('content')
<div class="{{ Auth::user()->isClient() ? 'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8' : 'max-w-5xl' }}">
    <!-- Top Status Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-2xl font-black text-slate-900">Hợp Đồng Booking #{{ $booking->id }}</h2>
                <x-booking-status :status="$booking->status" size="lg" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Khởi tạo lúc: <x-datetime :value="$booking->created_at" /></p>
        </div>

        @php
            $canManageBooking = Auth::user()->isAdmin()
                || (Auth::user()->isManager() && ($booking->manager_id === Auth::id() || $booking->streamer->manager_id === Auth::id()));
            // Booking::isRejectable() lives on the backend branch; fall back to the same rule (pending only).
            $isRejectable = method_exists($booking, 'isRejectable')
                ? $booking->isRejectable()
                : in_array($booking->status, ['pending'], true);
            $canRejectBooking = $canManageBooking && $isRejectable;
            $canApproveBooking = $canManageBooking && in_array($booking->status, ['pending', 'reviewing']);
        @endphp

        <!-- Approval / Rejection Actions for Manager / Admin -->
        @if($canRejectBooking || $canApproveBooking)
            <div class="flex items-center space-x-3">
                @if($canRejectBooking)
                <!-- Reject Button Trigger -->
                <button type="button" onclick="openRejectModal()" class="px-4 py-2.5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold text-xs transition">
                    <i class="fas fa-ban mr-1"></i> Từ Chối
                </button>
                @endif

                @if($canApproveBooking)
                <!-- Approve Button (with Collision Lock) -->
                <form action="{{ route('bookings.approve', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt và khóa lịch trình cho Talent này?');">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition flex items-center space-x-1.5">
                        <i class="fas fa-check-double"></i>
                        <span>Phê Duyệt & Khóa Lịch</span>
                    </button>
                </form>
                @endif
            </div>
        @endif
    </div>

    <!-- Collision Conflict Warning Banner for Manager -->
    @if($conflictInfo && $conflictInfo['has_conflict'])
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 mb-6 text-rose-900 shadow-sm">
            <div class="flex items-start space-x-3">
                <i class="fas fa-triangle-exclamation text-rose-600 text-2xl mt-0.5"></i>
                <div>
                    <h4 class="font-extrabold text-sm text-rose-800 uppercase tracking-wide">Phát Hiện Xung Đột Lịch Trình (Collision Detected!)</h4>
                    <p class="text-xs text-rose-700 mt-1">
                        {{ $conflictInfo['message'] }}
                    </p>
                    <p class="text-xs text-rose-600 font-semibold mt-2">
                        * Bạn cần từ chối đơn này hoặc yêu cầu khách hàng chọn khung giờ khác trước khi có thể duyệt.
                    </p>
                </div>
            </div>
        </div>
    @elseif(in_array($booking->status, ['pending', 'reviewing']))
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6 text-emerald-800 flex items-center shadow-sm text-xs font-semibold">
            <i class="fas fa-circle-check text-emerald-600 text-base mr-2"></i>
            <span>Khung giờ khả dụng: Streamer hoàn toàn không bị trùng lịch nào trong thời gian diễn ra chiến dịch.</span>
        </div>
    @endif

    <!-- Rejection Reason if Rejected -->
    @if($booking->status === 'rejected')
        @php
            // Backend may rename the column (rejection_reason); support both, null-safe.
            $rejectReason = trim((string) ($booking->rejection_reason ?? $booking->reject_reason ?? ''));
        @endphp
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 mb-6 text-rose-900" role="alert">
            <h4 class="font-bold text-sm text-rose-800 flex items-center mb-1">
                <i class="fas fa-circle-xmark mr-2 text-rose-600"></i> Booking đã bị từ chối
            </h4>
            @if($rejectReason !== '')
                <p class="text-xs text-rose-700 pl-6"><span class="font-semibold">Lý do:</span> {{ $rejectReason }}</p>
            @else
                <p class="text-xs text-rose-700 pl-6">Không có lý do cụ thể được ghi nhận.</p>
            @endif
        </div>
    @endif

    <!-- 2 Column Details: Parties & Financials -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Client & Streamer Info -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Thông Tin Các Bên</h3>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center text-lg">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Khách Hàng (Client)</span>
                    <div class="font-bold text-slate-900 text-sm">{{ $booking->client->name }}</div>
                    <span class="text-xs text-slate-500">{{ $booking->client->email }}</span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fas fa-video"></i>
                </div>
                <div class="flex-1">
                    <span class="text-xs text-slate-400 block">Talent Streamer</span>
                    <div class="font-bold text-slate-900 text-sm">{{ $booking->streamer->stage_name }}</div>
                    <span class="text-xs text-slate-500">{{ $booking->streamer->category }} • Giá: <x-money :amount="$booking->streamer->rate_per_hour" suffix="/giờ" /></span>
                </div>
                <a href="{{ route('talents.show', $booking->streamer->id) }}" target="_blank" class="text-xs font-semibold text-primary-600 hover:underline">
                    Media Kit
                </a>
            </div>

            <div class="text-xs text-slate-500 pt-2">
                <strong>Quản lý phụ trách duyệt:</strong> {{ $booking->manager->name ?? 'Đang chỉ định' }}
            </div>
        </div>

        <!-- Schedule & Financial Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Lịch Trình & Tài Chính</h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Bắt đầu:</span>
                    <span class="font-bold text-slate-800"><x-datetime :value="$booking->start_time" /></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Kết thúc:</span>
                    <span class="font-bold text-slate-800"><x-datetime :value="$booking->end_time" /></span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-500">Tổng thời lượng stream:</span>
                    <span class="font-bold text-indigo-600">{{ $booking->duration_hours }} giờ</span>
                </div>
            </div>

            <!-- Financial Breakdown -->
            <div class="p-4 rounded-xl bg-slate-900 text-white space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-300">Tổng ngân sách nhãn hàng chi trả:</span>
                    <span class="font-black text-emerald-400 text-sm"><x-money :amount="$booking->budget" /></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Hoa hồng Agency ({{ rtrim(rtrim(number_format((float) ($booking->commission_rate ?? config('mcn.commission_rate', 15)), 2, ',', '.'), '0'), ',') }}%):</span>
                    <span><x-money :amount="$booking->agency_commission" sign="- " /></span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-800 font-bold text-slate-200">
                    <span>Thù lao thực nhận của Streamer:</span>
                    <span class="text-amber-300 font-extrabold"><x-money :amount="$booking->streamer_net_income" /></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Brief -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Mô Tả Kịch Bản & Yêu Cầu Chiến Dịch</h3>
        <div class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed">
            {{ $booking->job_description }}
        </div>
    </div>
</div>

@if($canRejectBooking)
<!-- Modal Reject -->
<div id="reject-modal" class="{{ $errors->has('reject_reason') ? '' : 'hidden' }} fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 mb-2">Từ Chối Booking</h3>
        <p class="text-xs text-slate-500 mb-4">Vui lòng cung cấp lý do từ chối để hệ thống phản hồi đến đối tác.</p>
        
        <form action="{{ route('bookings.reject', $booking->id) }}" method="POST">
            @csrf
            <textarea name="reject_reason" rows="3" required minlength="5" maxlength="500" placeholder="Nhập lý do từ chối (trùng lịch, không phù hợp định hướng...)"
                @error('reject_reason') aria-invalid="true" @enderror
                class="w-full text-xs border {{ $errors->has('reject_reason') ? 'border-rose-400' : 'border-slate-300' }} rounded-xl p-3 focus:ring-2 focus:ring-rose-500 {{ $errors->has('reject_reason') ? 'mb-1' : 'mb-4' }}">{{ old('reject_reason') }}</textarea>
            @error('reject_reason')
                <p class="text-[11px] font-semibold text-rose-600 mb-4" role="alert">{{ $message }}</p>
            @enderror

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-sm">
                    Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal() {
    document.getElementById('reject-modal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}
</script>
@endif
@endsection
