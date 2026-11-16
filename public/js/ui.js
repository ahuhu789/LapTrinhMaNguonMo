/**
 * Public hamburger menu + off-canvas sidebar drawer (< 1024px).
 * Close on overlay click and Escape.
 */
(function () {
    const LG = '(min-width: 1024px)';

    function isDesktop() {
        return window.matchMedia(LG).matches;
    }

    function initPublicMenu() {
        const btn = document.getElementById('public-menu-toggle');
        const menu = document.getElementById('public-mobile-menu');
        if (!btn || !menu) return;

        const iconOpen = btn.querySelector('[data-icon-open]');
        const iconClose = btn.querySelector('[data-icon-close]');

        function setOpen(open) {
            if (isDesktop()) open = false;
            menu.classList.toggle('hidden', !open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (iconOpen) iconOpen.classList.toggle('hidden', open);
            if (iconClose) iconClose.classList.toggle('hidden', !open);
        }

        btn.addEventListener('click', function () {
            setOpen(menu.classList.contains('hidden'));
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
        window.addEventListener('resize', function () {
            if (isDesktop()) setOpen(false);
        });
    }

    function initSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggles = document.querySelectorAll('[data-sidebar-toggle]');
        if (!sidebar || !toggles.length) return;

        function setOpen(open) {
            if (isDesktop()) {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
                document.body.classList.remove('sidebar-open');
                toggles.forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
                return;
            }
            sidebar.classList.toggle('-translate-x-full', !open);
            if (overlay) overlay.classList.toggle('hidden', !open);
            document.body.classList.toggle('sidebar-open', open);
            toggles.forEach(function (t) { t.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        }

        toggles.forEach(function (t) {
            t.addEventListener('click', function () {
                const open = sidebar.classList.contains('-translate-x-full');
                setOpen(open);
            });
        });
        if (overlay) overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });
        window.addEventListener('resize', function () {
            if (isDesktop()) setOpen(false);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPublicMenu();
        initSidebar();
    });
})();
