document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function switchTab(tabName) {
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-tab') === tabName) btn.classList.add('active');
        });

        tabPanes.forEach(pane => {
            pane.classList.remove('active');
            if (pane.id === tabName) pane.classList.add('active');
        });

        localStorage.setItem('activeDashboardTab', tabName);
        if (tabName === 'overview' || tabName === 'analytics') {
            setTimeout(initCharts, 100);
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            switchTab(this.getAttribute('data-tab'));
        });
    });

    const savedTab = localStorage.getItem('activeDashboardTab') || 'overview';
    switchTab(savedTab);
    setTimeout(initCharts, 300);

    // Filter inventory
    window.filterInventory = function(event, filter) {
        const buttons = document.querySelectorAll('.filter-tab');
        const cards = document.querySelectorAll('.inventory-card');

        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            card.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
        });
    };
});

function initCharts() {
    if (typeof ApexCharts === 'undefined' || !window.DashboardData) return;

    const chartDefaults = {
        chart: { toolbar: { show: false }, animations: { enabled: true, easing: 'easeout', speed: 800 }, background: 'transparent' },
        theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
        dataLabels: { enabled: false },
        grid: { borderColor: 'var(--border-color)', strokeDashArray: 4 },
        tooltip: { theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light', borderRadius: 8 }
    };

    try {
        // Top Products
        const topProducts = window.DashboardData.topProducts || [];
        if (topProducts.length > 0 && !document.querySelector('#topProductsChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('topProductsChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'bar' },
                series: [{ name: 'Sold', data: topProducts.map(p => p.count) }],
                xaxis: { categories: topProducts.map(p => p.name.length > 12 ? p.name.substring(0, 12) + '...' : p.name) },
                colors: ['var(--primary)'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }).render();
        }

        // Stock Status
        const stockStatus = window.DashboardData.stockStatus || {};
        if (Object.values(stockStatus).some(v => v > 0) && !document.querySelector('#stockStatusChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('stockStatusChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'donut' },
                series: Object.values(stockStatus),
                labels: Object.keys(stockStatus),
                colors: ['#10B981', '#F59E0B', '#EF4444'],
                plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                legend: { position: 'bottom', fontSize: '12px' }
            }).render();
        }

        // Warehouse Chart
        const warehouseData = window.DashboardData.warehouseData || {};
        if (Object.keys(warehouseData).length > 0 && !document.querySelector('#warehouseChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('warehouseChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'bar' },
                series: [{ name: 'Stock', data: Object.values(warehouseData) }],
                xaxis: { categories: Object.keys(warehouseData) },
                colors: ['#3B82F6'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }).render();
        }

        // Category Chart
        const categoryData = window.DashboardData.categoryData || {};
        if (Object.keys(categoryData).length > 0 && !document.querySelector('#categoryChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('categoryChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 300, type: 'pie' },
                series: Object.values(categoryData),
                labels: Object.keys(categoryData),
                colors: ['var(--primary)', 'var(--primary-light)', '#2ecc71', '#3498db', '#9b59b6', '#f39c12', '#e74c3c', '#1abc9c', '#e67e22', '#95a5a6'],
                legend: { position: 'bottom', fontSize: '12px' }
            }).render();
        }

        // Analytics charts
        const topProductsDetail = window.DashboardData.topProductsDetail || [];
        if (topProductsDetail.length > 0 && !document.querySelector('#salesTrendChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('salesTrendChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'area' },
                series: [{ name: 'Sales', data: topProductsDetail.map(p => p.count) }],
                xaxis: { categories: topProductsDetail.map(p => p.name.length > 12 ? p.name.substring(0, 12) + '...' : p.name) },
                colors: ['var(--primary-light)'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
                stroke: { curve: 'smooth', width: 3 }
            }).render();
        }

        const warehouseDetail = window.DashboardData.warehouseData || {};
        if (Object.keys(warehouseDetail).length > 0 && !document.querySelector('#warehouseDetailChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('warehouseDetailChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'column' },
                series: [{ name: 'Stock', data: Object.values(warehouseDetail) }],
                xaxis: { categories: Object.keys(warehouseDetail) },
                colors: ['#16a085'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }).render();
        }

        const categoryDetail = window.DashboardData.categoryData || {};
        if (Object.keys(categoryDetail).length > 0 && !document.querySelector('#categoryDetailChart.apexcharts-canvas')) {
            new ApexCharts(document.getElementById('categoryDetailChart'), {
                ...chartDefaults, chart: { ...chartDefaults.chart, height: 350, type: 'bar' },
                series: [{ name: 'Products', data: Object.values(categoryDetail) }],
                xaxis: { categories: Object.keys(categoryDetail) },
                colors: ['#9b59b6'],
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%' } }
            }).render();
        }

    } catch (error) {
        console.error('Chart error:', error);
    }
}
