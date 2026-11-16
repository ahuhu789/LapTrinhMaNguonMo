/**
 * Reusable danger confirm modal. Triggers: [data-confirm] (see x-confirm-button).
 * Never uses window.confirm().
 */
(function () {
    function qs(sel, root) { return (root || document).querySelector(sel); }

    function init() {
        const modal = qs('#confirm-modal');
        if (!modal) return;
        const form = qs('#confirm-modal-form', modal);
        const title = qs('#confirm-modal-title', modal);
        const message = qs('#confirm-modal-message', modal);
        const methodInput = qs('#confirm-modal-method', modal);
        const reasonWrap = qs('#confirm-modal-reason-wrap', modal);
        const reasonInput = qs('#confirm-modal-reason', modal);
        const reasonLabel = qs('#confirm-modal-reason-label', modal);
        const reasonError = qs('#confirm-modal-reason-error', modal);
        const submitBtn = qs('#confirm-modal-submit', modal);
        const overlay = qs('[data-confirm-overlay]', modal);
        const cancelBtn = qs('[data-confirm-cancel]', modal);

        function close() {
            modal.classList.add('hidden');
        }

        function openFrom(trigger) {
            form.action = trigger.getAttribute('data-confirm-action') || '#';
            const method = (trigger.getAttribute('data-confirm-method') || 'POST').toUpperCase();
            if (method === 'POST') {
                methodInput.disabled = true;
                methodInput.value = 'POST';
            } else {
                methodInput.disabled = false;
                methodInput.value = method;
            }
            title.textContent = trigger.getAttribute('data-confirm-title') || 'Xác nhận';
            message.textContent = trigger.getAttribute('data-confirm-message') || '';
            submitBtn.textContent = trigger.getAttribute('data-confirm-label') || 'Xác nhận';
            const variant = trigger.getAttribute('data-confirm-variant') || 'danger';
            submitBtn.className = variant === 'success'
                ? 'px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm'
                : (variant === 'primary'
                    ? 'px-4 py-2 text-xs font-bold bg-primary-600 hover:bg-primary-700 text-white rounded-xl shadow-sm'
                    : 'px-4 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-sm');

            const needsReason = trigger.getAttribute('data-confirm-reason') === '1'
                || trigger.getAttribute('data-confirm-reason') === 'true';
            reasonWrap.classList.toggle('hidden', !needsReason);
            reasonInput.required = needsReason;
            reasonInput.name = trigger.getAttribute('data-confirm-reason-name') || 'cancel_reason';
            reasonInput.value = '';
            reasonInput.classList.remove('border-rose-400');
            reasonError.classList.add('hidden');
            const labelText = trigger.getAttribute('data-confirm-reason-label') || 'Lý do';
            reasonLabel.innerHTML = labelText + ' <span class="text-rose-500">*</span>';
            const ph = trigger.getAttribute('data-confirm-reason-placeholder');
            if (ph) reasonInput.placeholder = ph;

            modal.classList.remove('hidden');
            setTimeout(function () {
                (needsReason ? reasonInput : submitBtn).focus();
            }, 50);
        }

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-confirm]');
            if (!trigger) return;
            e.preventDefault();
            openFrom(trigger);
        });

        overlay && overlay.addEventListener('click', close);
        cancelBtn && cancelBtn.addEventListener('click', close);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                e.stopPropagation();
                close();
            }
        });

        form.addEventListener('submit', function (e) {
            if (reasonWrap.classList.contains('hidden')) return;
            const val = (reasonInput.value || '').trim();
            if (val.length < 5) {
                e.preventDefault();
                reasonInput.classList.add('border-rose-400');
                reasonError.classList.remove('hidden');
                reasonInput.focus();
            }
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
