{{-- Reusable danger confirm modal. Opened by [data-confirm] triggers (see public/js/confirm-modal.js). No native confirm(). --}}
<div id="confirm-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-confirm-overlay></div>
    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 id="confirm-modal-title" class="text-lg font-bold text-slate-900 mb-2">Xác nhận</h3>
        <p id="confirm-modal-message" class="text-xs text-slate-500 mb-4"></p>

        <form id="confirm-modal-form" method="POST" action="#">
            @csrf
            <input type="hidden" name="_method" id="confirm-modal-method" value="POST" disabled>

            <div id="confirm-modal-reason-wrap" class="hidden mb-4">
                <label id="confirm-modal-reason-label" for="confirm-modal-reason" class="block text-xs font-semibold text-slate-700 mb-1">Lý do <span class="text-rose-500">*</span></label>
                <textarea id="confirm-modal-reason" name="cancel_reason" rows="3" minlength="5" maxlength="500"
                    class="w-full text-xs border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-rose-500"
                    placeholder="Nhập lý do (tối thiểu 5 ký tự)..."></textarea>
                <p id="confirm-modal-reason-error" class="hidden mt-1 text-[11px] font-semibold text-rose-600" role="alert">Vui lòng nhập lý do (tối thiểu 5 ký tự).</p>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" data-confirm-cancel class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                    Hủy bỏ
                </button>
                <button type="submit" id="confirm-modal-submit" class="px-4 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-sm">
                    Xác nhận
                </button>
            </div>
        </form>
    </div>
</div>
