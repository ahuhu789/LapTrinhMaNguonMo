// Main Application JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Auto fadeout flash alerts after 5 seconds
    const alerts = document.querySelectorAll('.flash-alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
