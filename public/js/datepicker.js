/**
 * Khởi tạo flatpickr cho mọi input có data-datepicker="datetime|date|month".
 * Giá trị gửi lên server: 'Y-m-d H:i' | 'Y-m-d' | 'Y-m' (server validate bằng rule 'date' / date_format:Y-m).
 * Hiển thị (altInput): 'd/m/Y H:i' | 'd/m/Y' | 'm/Y'.
 * Input gốc vẫn giữ id/name => JS khác (live check booking) đọc .value như bình thường;
 * mỗi lần chọn sẽ phát sự kiện 'input' + 'change' trên input gốc.
 */
(function () {
    function normalize(v, mode) {
        if (!v) return v;
        v = String(v).trim().replace('T', ' ');
        if (mode === 'datetime') return v.slice(0, 16);
        if (mode === 'date') return v.slice(0, 10);
        if (mode === 'month') return v.slice(0, 7);
        return v;
    }

    function init(el) {
        if (el._flatpickr || typeof window.flatpickr !== 'function') return;
        const mode = el.dataset.datepicker || 'datetime';
        el.value = normalize(el.value, mode);
        const locale = (window.flatpickr.l10ns && window.flatpickr.l10ns.vn) || 'default';
        const opts = {
            locale: locale,
            allowInput: true,
            altInput: true,
            disableMobile: true,
            onChange: function (_d, _s, fp) {
                fp.input.dispatchEvent(new Event('input', { bubbles: true }));
                fp.input.dispatchEvent(new Event('change', { bubbles: true }));
            },
        };
        if (mode === 'month') {
            Object.assign(opts, { dateFormat: 'Y-m', altFormat: 'm/Y' });
            if (typeof window.monthSelectPlugin === 'function') {
                opts.plugins = [new window.monthSelectPlugin({ shorthand: false, dateFormat: 'Y-m', altFormat: 'm/Y' })];
            }
        } else if (mode === 'date') {
            Object.assign(opts, { dateFormat: 'Y-m-d', altFormat: 'd/m/Y' });
        } else {
            Object.assign(opts, { enableTime: true, time_24hr: true, minuteIncrement: 15, dateFormat: 'Y-m-d H:i', altFormat: 'd/m/Y H:i' });
        }
        if (el.dataset.minDate) opts.minDate = el.dataset.minDate;
        const fp = window.flatpickr(el, opts);
        if (fp.altInput) {
            fp.altInput.placeholder = el.getAttribute('placeholder') || (mode === 'month' ? 'mm/yyyy' : mode === 'date' ? 'dd/mm/yyyy' : 'dd/mm/yyyy HH:mm');
            if (el.required) fp.altInput.required = true;
            if (el.id) fp.altInput.setAttribute('aria-labelledby', el.id);
        }
    }

    function initAll(root) {
        (root || document).querySelectorAll('input[data-datepicker]').forEach(init);
    }
    window.initDatepickers = initAll;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initAll(); });
    else initAll();
})();
