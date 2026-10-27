@extends('layouts.app')

@section('title', 'Tạo Yêu Cầu Booking Talent - MCN Platform')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 p-8 text-white">
            <div class="inline-flex items-center space-x-2 bg-white/20 px-3 py-1 rounded-full text-xs font-semibold mb-3">
                <i class="fas fa-handshake"></i>
                <span>Hợp Tác Quảng Bá Thương Hiệu</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black">Gửi Yêu Cầu Booking Streamer</h1>
            <p class="text-primary-100 text-sm mt-1">Hệ thống áp dụng thuật toán Collision-Free tự động kiểm tra xung đột thời gian.</p>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST" class="p-8 space-y-6">
            @csrf

            <!-- Select Streamer -->
            <div>
                <label for="streamer_id" class="block text-sm font-bold text-slate-700 mb-2">
                    1. Chọn Streamer Muốn Hợp Tác <span class="text-rose-500">*</span>
                </label>
                <select name="streamer_id" id="streamer_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm font-medium">
                    <option value="">-- Chọn một Creator trong hệ sinh thái --</option>
                    @foreach($streamers as $st)
                        <option value="{{ $st->id }}" 
                            data-rate="{{ $st->rate_per_hour }}"
                            {{ (old('streamer_id', $selectedStreamer->id ?? '') == $st->id) ? 'selected' : '' }}>
                            {{ $st->stage_name }} ({{ $st->category }}) — {{ number_format((float) $st->rate_per_hour, 0, ',', '.') }} ₫/giờ
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Time Selection with Collision Hint -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-bold text-slate-700 mb-2">
                        2. Thời Gian Bắt Đầu <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-bold text-slate-700 mb-2">
                        3. Thời Gian Kết Thúc <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm">
                </div>
            </div>

            <!-- Live Collision Feedback Box -->
            <div id="collision-alert" class="hidden p-4 rounded-xl text-xs font-semibold flex items-center">
                <i id="collision-icon" class="mr-2 text-base"></i>
                <span id="collision-text"></span>
            </div>

            <!-- Budget -->
            <div>
                <label for="budget" class="block text-sm font-bold text-slate-700 mb-2">
                    4. Tổng Ngân Sách Dự Kiến (₫) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="budget" id="budget" value="{{ old('budget', '3000000') }}" min="100000" step="50000" required
                        placeholder="VD: 3000000"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm font-bold text-slate-800">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-xs font-bold text-slate-400">₫</span>
                </div>
                {{-- mcn.commission_rate is a percentage (e.g. 15.00 = 15%); default matches backend config/mcn.php --}}
                <p class="text-xs text-slate-400 mt-1">Đã bao gồm phí hoa hồng quản lý agency {{ rtrim(rtrim(number_format((float) config('mcn.commission_rate', 15), 2, ',', '.'), '0'), ',') }}%.</p>
            </div>

            <!-- Campaign Brief -->
            <div>
                <label for="job_description" class="block text-sm font-bold text-slate-700 mb-2">
                    5. Mô Tả Chiến Dịch / Kịch Bản Livestream <span class="text-rose-500">*</span>
                </label>
                <textarea name="job_description" id="job_description" rows="4" required
                    placeholder="Mô tả chi tiết sản phẩm cần quảng bá, thông điệp truyền thông, thời lượng nói về thương hiệu, mã voucher giảm giá nếu có..."
                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 text-sm">{{ old('job_description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ url()->previous() }}" class="text-slate-500 hover:text-slate-700 text-sm font-semibold">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </a>
                <button type="submit" class="px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 transition flex items-center space-x-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Gửi Yêu Cầu Booking</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const streamerSelect = document.getElementById('streamer_id');
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');
    const alertBox = document.getElementById('collision-alert');
    const alertIcon = document.getElementById('collision-icon');
    const alertText = document.getElementById('collision-text');

    // Authenticated web route (auth + CSRF). Fallback URL keeps the page rendering
    // on branches where the named route is not registered yet.
    const collisionUrl = {{ Js::from(Route::has('bookings.check-collision') ? route('bookings.check-collision') : url('/bookings/check-collision')) }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const STYLE_CLASSES = ['bg-rose-50', 'text-rose-800', 'border-rose-200', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'bg-amber-50', 'text-amber-800', 'border-amber-200'];
    const STYLES = {
        conflict: { box: ['bg-rose-50', 'text-rose-800', 'border-rose-200'], icon: 'fas fa-triangle-exclamation mr-2 text-rose-600' },
        ok:       { box: ['bg-emerald-50', 'text-emerald-800', 'border-emerald-200'], icon: 'fas fa-circle-check mr-2 text-emerald-600' },
        warning:  { box: ['bg-amber-50', 'text-amber-800', 'border-amber-200'], icon: 'fas fa-circle-info mr-2 text-amber-600' },
    };

    function showAlert(type, message) {
        const style = STYLES[type] || STYLES.warning;
        alertBox.classList.remove('hidden', 'border', ...STYLE_CLASSES);
        alertBox.classList.add('border', ...style.box);
        alertIcon.className = style.icon;
        alertText.textContent = message; // textContent: never interpret server text as HTML
    }

    function firstValidationMessage(data) {
        if (data && data.errors && typeof data.errors === 'object') {
            for (const key of Object.keys(data.errors)) {
                const msgs = data.errors[key];
                if (Array.isArray(msgs) && msgs.length) return String(msgs[0]);
                if (typeof msgs === 'string' && msgs) return msgs;
            }
        }
        return '';
    }

    let requestSeq = 0;

    async function checkLiveCollision() {
        const streamerId = streamerSelect.value;
        const startTime = startInput.value;
        const endTime = endInput.value;

        if (!streamerId || !startTime || !endTime) {
            alertBox.classList.add('hidden');
            return;
        }

        const seq = ++requestSeq; // ignore out-of-order responses

        let res;
        try {
            res = await fetch(collisionUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ streamer_id: streamerId, start_time: startTime, end_time: endTime })
            });
        } catch (e) {
            if (seq !== requestSeq) return;
            console.error(e);
            showAlert('warning', 'Không thể kiểm tra lịch trống lúc này (lỗi kết nối). Bạn vẫn có thể gửi yêu cầu, hệ thống sẽ kiểm tra lại khi lưu.');
            return;
        }

        let data = null;
        try { data = await res.json(); } catch (e) { data = null; }
        if (seq !== requestSeq) return;

        if (res.ok) {
            if (data && data.has_conflict === true) {
                showAlert('conflict', 'Cảnh báo xung đột: Streamer đã có lịch trong khung giờ này. Vui lòng chọn thời gian khác.');
            } else if (data && data.has_conflict === false) {
                showAlert('ok', 'Khung giờ hoàn toàn khả dụng! Streamer đang trống lịch.');
            } else {
                showAlert('warning', 'Không đọc được kết quả kiểm tra lịch. Vui lòng thử lại.');
            }
            return;
        }

        switch (res.status) {
            case 422:
                showAlert('warning', firstValidationMessage(data) || 'Thông tin chưa hợp lệ. Vui lòng kiểm tra lại Streamer và thời gian bắt đầu/kết thúc.');
                break;
            case 401:
                showAlert('warning', 'Bạn cần đăng nhập để kiểm tra lịch trống. Vui lòng đăng nhập lại.');
                break;
            case 419:
                showAlert('warning', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang (F5) rồi thử lại.');
                break;
            case 429:
                showAlert('warning', 'Bạn thao tác quá nhanh. Vui lòng đợi một lát rồi thử lại.');
                break;
            default:
                showAlert('warning', 'Không thể kiểm tra lịch trống lúc này. Vui lòng thử lại sau.');
        }
    }

    startInput.addEventListener('change', checkLiveCollision);
    endInput.addEventListener('change', checkLiveCollision);
    streamerSelect.addEventListener('change', checkLiveCollision);
});
</script>
@endsection
