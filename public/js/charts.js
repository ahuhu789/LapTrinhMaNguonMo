// Chart.js helper utilities
window.McnCharts = {
    createViewerTrendChart: function(elementId, labels, avgData, peakData) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Lượng xem đỉnh cao (Peak)',
                        data: peakData,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                    },
                    {
                        label: 'Lượng xem trung bình (Avg)',
                        data: avgData,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });
    }
};
