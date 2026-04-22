document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const renderedCharts = new Map();

    function getThemeMode() {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    }

    function getCSSVar(name) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    function getChartPalette() {
        return [
            getCSSVar('--primary'),
            getCSSVar('--primary-light'),
            '#34d399',
            '#f59e0b',
            '#f97316',
            '#ef4444',
            '#5eead4',
            '#99f6e4',
        ];
    }

    function destroyCharts() {
        renderedCharts.forEach((chart) => chart.destroy());
        renderedCharts.clear();
    }

    function renderChart(id, options) {
        const target = document.getElementById(id);
        if (!target || renderedCharts.has(id) || typeof ApexCharts === 'undefined') {
            return;
        }

        const chart = new ApexCharts(target, options);
        chart.render();
        renderedCharts.set(id, chart);
    }

    function renderCharts() {
        if (!window.DashboardData || typeof ApexCharts === 'undefined') {
            return;
        }

        const primary = getCSSVar('--primary');
        const primaryLight = getCSSVar('--primary-light');
        const textPrimary = getCSSVar('--text-primary');
        const textSecondary = getCSSVar('--text-secondary');
        const borderColor = getCSSVar('--border-color');
        const palette = getChartPalette();

        const base = {
            chart: {
                toolbar: { show: false },
                background: 'transparent',
                animations: { enabled: true, easing: 'easeout', speed: 500 },
            },
            theme: { mode: getThemeMode() },
            grid: { borderColor, strokeDashArray: 4 },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                labels: { colors: textSecondary },
            },
            tooltip: {
                theme: getThemeMode(),
            },
            xaxis: {
                labels: { style: { colors: textSecondary } },
            },
            yaxis: {
                labels: { style: { colors: textSecondary } },
            },
        };

        const topProducts = window.DashboardData.topProducts || [];
        if (topProducts.length) {
            renderChart('topProductsChart', {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 320 },
                series: [{ name: 'Sold', data: topProducts.map((item) => item.count) }],
                colors: [primary],
                plotOptions: { bar: { borderRadius: 10, columnWidth: '56%' } },
                xaxis: {
                    ...base.xaxis,
                    categories: topProducts.map((item) => item.name.length > 14 ? `${item.name.slice(0, 14)}…` : item.name),
                },
            });
        }

        const stockStatus = window.DashboardData.stockStatus || {};
        if (Object.values(stockStatus).some((value) => value > 0)) {
            renderChart('stockStatusChart', {
                ...base,
                chart: { ...base.chart, type: 'donut', height: 320 },
                series: Object.values(stockStatus),
                labels: Object.keys(stockStatus),
                colors: [primary, '#f59e0b', '#ef4444'],
                stroke: { colors: [getCSSVar('--bg-secondary')] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: textSecondary,
                                },
                            },
                        },
                    },
                },
            });
        }

        const warehouseData = window.DashboardData.warehouseData || {};
        if (Object.keys(warehouseData).length) {
            renderChart('warehouseChart', {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 320 },
                series: [{ name: 'Stock', data: Object.values(warehouseData) }],
                colors: [primaryLight],
                plotOptions: { bar: { borderRadius: 10, columnWidth: '52%' } },
                xaxis: { ...base.xaxis, categories: Object.keys(warehouseData) },
            });

            renderChart('warehouseDetailChart', {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 380 },
                series: [{ name: 'Stock', data: Object.values(warehouseData) }],
                colors: [primary],
                plotOptions: { bar: { borderRadius: 10, columnWidth: '48%' } },
                xaxis: { ...base.xaxis, categories: Object.keys(warehouseData) },
            });
        }

        const categoryData = window.DashboardData.categoryData || {};
        if (Object.keys(categoryData).length) {
            renderChart('categoryChart', {
                ...base,
                chart: { ...base.chart, type: 'donut', height: 320 },
                series: Object.values(categoryData),
                labels: Object.keys(categoryData),
                colors: palette,
                stroke: { colors: [getCSSVar('--bg-secondary')] },
            });

            renderChart('categoryDetailChart', {
                ...base,
                chart: { ...base.chart, type: 'bar', height: 380 },
                series: [{ name: 'Products', data: Object.values(categoryData) }],
                colors: [primary],
                plotOptions: { bar: { horizontal: true, borderRadius: 8 } },
                xaxis: { ...base.xaxis, categories: Object.keys(categoryData) },
            });
        }

        const topProductsDetail = window.DashboardData.topProductsDetail || [];
        if (topProductsDetail.length) {
            renderChart('salesTrendChart', {
                ...base,
                chart: { ...base.chart, type: 'area', height: 380 },
                series: [{ name: 'Sales', data: topProductsDetail.map((item) => item.count) }],
                colors: [primary],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        opacityFrom: 0.34,
                        opacityTo: 0.06,
                    },
                },
                xaxis: {
                    ...base.xaxis,
                    categories: topProductsDetail.map((item) => item.name.length > 14 ? `${item.name.slice(0, 14)}…` : item.name),
                },
            });
        }
    }

    function switchTab(tabName) {
        tabButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        tabPanes.forEach((pane) => {
            pane.classList.toggle('active', pane.id === tabName);
        });

        localStorage.setItem('activeDashboardTab', tabName);
        setTimeout(renderCharts, 80);
    }

    window.filterInventory = (event, filter) => {
        document.querySelectorAll('.filter-tab').forEach((button) => {
            button.classList.toggle('active', button === event.currentTarget);
        });

        document.querySelectorAll('.inventory-card').forEach((card) => {
            const status = card.getAttribute('data-status');
            card.style.display = filter === 'all' || status === filter ? '' : 'none';
        });
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => switchTab(button.dataset.tab));
    });

    const savedTab = localStorage.getItem('activeDashboardTab') || 'overview';
    switchTab(savedTab);

    const observer = new MutationObserver(() => {
        destroyCharts();
        setTimeout(renderCharts, 80);
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'style'] });
});
