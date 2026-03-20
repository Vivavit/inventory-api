/**
 * Charts Module
 * Initializes and manages ApexCharts visualizations
 */

export function initInventoryTrendChart() {
    // Check if ApexCharts is available
    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts not loaded. Please include ApexCharts library.');
        return;
    }

    const chartElement = document.getElementById('chart-placeholder');
    if (!chartElement) return;

    // Sample data for inventory trends
    const dailyData = {
        series: [
            {
                name: 'Stock Count',
                data: [1250, 1280, 1300, 1320, 1290, 1310, 1340, 1360, 1380, 1400]
            }
        ],
        categories: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8', 'Day 9', 'Day 10']
    };

    const weeklyData = {
        series: [
            {
                name: 'Stock Count',
                data: [1250, 1320, 1380, 1420, 1390, 1450, 1500]
            }
        ],
        categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7']
    };

    const monthlyData = {
        series: [
            {
                name: 'Stock Count',
                data: [1100, 1250, 1380, 1450, 1420, 1500, 1580, 1620, 1650, 1700, 1750, 1800]
            }
        ],
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    };

    // Chart options
    const baseOptions = {
        chart: {
            type: 'line',
            height: 300,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 2,
                blur: 4,
                opacity: 0.1
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#0fb9b1']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 100]
            }
        },
        colors: ['#0fb9b1'],
        xaxis: {
            labels: {
                style: {
                    colors: '#999',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#999',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#e9ecef',
            strokeDashArray: 3,
            xaxis: {
                lines: {
                    show: false
                }
            }
        },
        tooltip: {
            theme: 'light',
            style: {
                fontSize: '12px'
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: {
                colors: '#333'
            }
        }
    };

    // Initialize with daily data
    let currentChart = new ApexCharts(chartElement, {
        ...baseOptions,
        series: dailyData.series,
        xaxis: {
            ...baseOptions.xaxis,
            categories: dailyData.categories
        }
    });

    currentChart.render();

    // Store chart instance globally for tab switching
    window.inventoryChart = currentChart;
    window.chartData = { dailyData, weeklyData, monthlyData };
}

export function switchInventoryChart(type) {
    if (!window.inventoryChart || !window.chartData) return;

    let newData;
    switch (type) {
        case 'weekly':
            newData = window.chartData.weeklyData;
            break;
        case 'monthly':
            newData = window.chartData.monthlyData;
            break;
        default:
            newData = window.chartData.dailyData;
    }

    window.inventoryChart.updateOptions({
        series: newData.series,
        xaxis: {
            categories: newData.categories
        }
    });
}

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Load ApexCharts library from CDN if not already loaded
    if (typeof ApexCharts === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js';
        script.onload = function () {
            initInventoryTrendChart();
        };
        document.head.appendChild(script);
    } else {
        initInventoryTrendChart();
    }

    // Attach chart switching to global window object for inline onclick handlers
    window.switchChart = switchInventoryChart;
});
