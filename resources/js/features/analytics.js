/**
 * Inventory Analytics Dashboard
 */

const Alpine = window.Alpine;

// Theme Color Manager
class ThemeColorManager {
    constructor() {
        this.isDark = document.documentElement.classList.contains('dark');
        this.observeThemeChanges();
    }

    observeThemeChanges() {
        const observer = new MutationObserver(() => {
            this.isDark = document.documentElement.classList.contains('dark');
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    getComputedVar(varName) {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    getPrimaryColor() {
        return this.getComputedVar('--primary') || '#0D9488';
    }

    getSecondaryColor() {
        return this.getComputedVar('--primary-light') || '#2DD4BF';
    }

    getTextColor() {
        return this.isDark ? '#e8f5f2' : '#1f2937';
    }

    getGridColor() {
        return this.isDark ? 'rgba(15, 185, 177, 0.08)' : 'rgba(3, 98, 76, 0.06)';
    }

    getTooltipBg() {
        return this.isDark ? '#122b24' : '#ffffff';
    }

    getTooltipText() {
        return this.isDark ? '#e8f5f2' : '#1f2937';
    }

    getChartColors() {
        return [
            this.getPrimaryColor(),
            this.getSecondaryColor(),
            '#34d399',
            '#f59e0b',
            '#f97316',
            '#ef4444',
            '#5eead4',
            '#99f6e4'
        ];
    }
}

// Chart Manager - Handles all Chart.js instances
class ChartManager {
    constructor() {
        this.charts = new Map();
        this.themeManager = new ThemeColorManager();
        this.updateDefaultOptions();
    }

    updateDefaultOptions() {
        this.defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 500, easing: 'easeInOutQuart' },
            plugins: {
                legend: {
                    labels: { 
                        color: this.themeManager.getTextColor(),
                        font: { size: 11, family: 'system-ui' },
                        padding: 12,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: this.themeManager.getTooltipBg(),
                    titleColor: this.themeManager.getTextColor(),
                    bodyColor: this.themeManager.getTextColor(),
                    padding: 12,
                    cornerRadius: 8,
                    borderColor: this.themeManager.getPrimaryColor(),
                    borderWidth: 1
                }
            }
        };
    }

    create(id, config) {
        this.destroy(id);
        
        const canvas = document.getElementById(id);
        if (!canvas) return null;
        
        const chart = new Chart(canvas, {
            ...config,
            options: { ...this.defaultOptions, ...config.options }
        });
        
        this.charts.set(id, chart);
        return chart;
    }

    destroy(id) {
        const chart = this.charts.get(id);
        if (chart) {
            chart.destroy();
            this.charts.delete(id);
        }
    }

    destroyAll() {
        this.charts.forEach((chart) => chart.destroy());
        this.charts.clear();
    }

    getAxisConfig() {
        return {
            grid: { 
                color: this.themeManager.getGridColor(),
                drawBorder: false 
            },
            ticks: { 
                color: this.themeManager.getTextColor(),
                font: { size: 11 }
            }
        };
    }
}

// API Service
class AnalyticsAPI {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.abortController = null;
    }

    async fetchData(period) {
        // Cancel previous request
        if (this.abortController) {
            this.abortController.abort();
        }
        
        this.abortController = new AbortController();
        
        try {
            const response = await fetch(`${this.baseURL}/${period}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: this.abortController.signal
            });
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Request cancelled');
                return null;
            }
            throw error;
        }
    }
}

// Alpine.js Application
document.addEventListener('alpine:init', () => {
    if (!Alpine) {
        return;
    }
    Alpine.data('analyticsApp', () => ({
        // State
        activePeriod: window.ANALYTICS_CONFIG?.initialPeriod || 'month',
        activeTab: 'overview',
        isLoading: false,
        lastUpdated: null,
        data: null,
        chartsRendered: {
            overview: false,
            trends: false,
            warehouse: false,
            reports: false
        },
        
        // Managers
        chartManager: new ChartManager(),
        api: new AnalyticsAPI('/api/analytics'),
        
        // Configuration
        periods: [
            { value: 'day', label: 'Today' },
            { value: 'week', label: 'This Week' },
            { value: 'month', label: 'This Month' },
            { value: 'year', label: 'This Year' }
        ],
        
        tabs: [
            { id: 'overview', label: 'Overview' },
            { id: 'trends', label: 'Trends' },
            { id: 'warehouse', label: 'Warehouse' },
            { id: 'reports', label: 'Reports' }
        ],
        
        // Computed properties
        get lastUpdatedText() {
            if (!this.lastUpdated) return 'Never updated';
            const time = this.lastUpdated.toLocaleTimeString([], { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            return `Updated ${time}`;
        },
        
        get indicatorStyle() {
            const activeButton = document.querySelector(`.tab-button[data-tab="${this.activeTab}"]`);
            if (!activeButton) return {};
            
            const container = activeButton.closest('.tab-nav-container');
            const containerRect = container.getBoundingClientRect();
            const buttonRect = activeButton.getBoundingClientRect();
            
            return {
                left: `${buttonRect.left - containerRect.left}px`,
                width: `${buttonRect.width}px`
            };
        },
        
        get kpiCards() {
            const kpis = this.data?.kpis || {};
            return [
                {
                    key: 'average_stock_value',
                    label: 'Avg Stock Value',
                    value: kpis.average_stock_value || 0,
                    icon: 'bi-currency-dollar',
                    colorClass: 'teal',
                    subtitle: 'Inventory replacement cost',
                    trend: this.calculateTrend('stock_value'),
                    trendDirection: this.getTrendDirection('stock_value')
                },
                {
                    key: 'low_stock_alert_count',
                    label: 'Low Stock Alerts',
                    value: kpis.low_stock_alert_count || 0,
                    icon: 'bi-exclamation-triangle',
                    colorClass: 'warning',
                    subtitle: 'Items near reorder point',
                    trend: null
                },
                {
                    key: 'out_of_stock_count',
                    label: 'Out of Stock',
                    value: kpis.out_of_stock_count || 0,
                    icon: 'bi-x-circle',
                    colorClass: 'danger',
                    subtitle: 'Products needing restock',
                    trend: null
                },
                {
                    key: 'total_sales_today',
                    label: 'Sales Today',
                    value: kpis.total_sales_today || 0,
                    icon: 'bi-bag-check',
                    colorClass: 'primary',
                    subtitle: 'Gross order total',
                    trend: this.calculateTrend('sales_daily'),
                    trendDirection: this.getTrendDirection('sales_daily')
                },
                {
                    key: 'total_sales_month',
                    label: 'This Month',
                    value: kpis.total_sales_month || 0,
                    icon: 'bi-calendar-check',
                    colorClass: 'primary',
                    subtitle: 'Monthly revenue',
                    trend: this.calculateTrend('sales_monthly'),
                    trendDirection: this.getTrendDirection('sales_monthly')
                },
                {
                    key: 'total_sales_year',
                    label: 'This Year',
                    value: kpis.total_sales_year || 0,
                    icon: 'bi-graph-up',
                    colorClass: 'success',
                    subtitle: 'Year-to-date revenue',
                    trend: this.calculateTrend('sales_yearly'),
                    trendDirection: this.getTrendDirection('sales_yearly')
                }
            ];
        },
        
        get warehouseData() {
            return this.data?.warehouse?.rows || [];
        },
        
        get topProducts() {
            return this.data?.top_products || [];
        },
        
        get lowStockItems() {
            return this.data?.low_stock || [];
        },
        
        get categorySummary() {
            return this.data?.category_summary || [];
        },
        
        // Lifecycle
        init() {
            this.loadData();
            this.setupEventListeners();
        },
        
        setupEventListeners() {
            window.addEventListener('resize', () => {
                this.updateIndicator();
            });
        },
        
        updateIndicator() {
            // Force reactive update
            this.activeTab = this.activeTab;
        },
        
        // Actions
        async loadData() {
            this.isLoading = true;
            
            try {
                this.data = await this.api.fetchData(this.activePeriod);
                this.lastUpdated = new Date();
                
                // Render current tab
                await this.renderCurrentTab();
            } catch (error) {
                console.error('Failed to load analytics:', error);
                // Show error toast (implement as needed)
            } finally {
                this.isLoading = false;
            }
        },
        
        async setPeriod(period) {
            if (this.activePeriod === period) return;
            
            this.activePeriod = period;
            this.chartsRendered = {
                overview: false,
                trends: false,
                warehouse: false,
                reports: false
            };
            
            await this.loadData();
        },
        
        async switchTab(tabId) {
            if (this.activeTab === tabId) return;
            
            this.activeTab = tabId;
            this.updateIndicator();
            
            await this.renderCurrentTab();
        },
        
        async renderCurrentTab() {
            if (!this.data) return;
            
            const tabRendered = this.chartsRendered[this.activeTab];
            if (tabRendered) return;
            
            await this.$nextTick();
            
            switch (this.activeTab) {
                case 'overview':
                    this.renderOverviewCharts();
                    break;
                case 'trends':
                    this.renderTrendsCharts();
                    break;
                case 'warehouse':
                    this.renderWarehouseCharts();
                    break;
                case 'reports':
                    // Tables only, no charts to render
                    break;
            }
            
            this.chartsRendered[this.activeTab] = true;
        },
        
        renderOverviewCharts() {
            const { stock_value_trend, category_distribution, sales_trend } = this.data;
            const colors = this.chartManager.themeManager;
            
            // Stock Value Chart
            this.chartManager.create('stockValueChart', {
                type: 'line',
                data: {
                    labels: stock_value_trend?.labels || [],
                    datasets: [{
                        label: 'Stock Value',
                        data: stock_value_trend?.values || [],
                        borderColor: colors.getPrimaryColor(),
                        backgroundColor: this.createGradient('stockValueChart', [
                            'rgba(15, 185, 177, 0.15)', 
                            'rgba(15, 185, 177, 0)'
                        ]),
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    scales: {
                        x: this.chartManager.getAxisConfig(),
                        y: {
                            ...this.chartManager.getAxisConfig(),
                            ticks: {
                                ...this.chartManager.getAxisConfig().ticks,
                                callback: (value) => this.formatCurrency(value)
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            
            // Category Distribution Chart
            this.chartManager.create('categoryDistributionChart', {
                type: 'doughnut',
                data: {
                    labels: category_distribution?.labels || [],
                    datasets: [{
                        data: category_distribution?.values || [],
                        backgroundColor: colors.getChartColors(),
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
            
            // Sales Trend Chart
            this.chartManager.create('salesTrendChart', {
                type: 'bar',
                data: {
                    labels: sales_trend?.labels || [],
                    datasets: [{
                        label: 'Sales',
                        data: sales_trend?.values || [],
                        backgroundColor: this.createGradient('salesTrendChart', [
                            'rgba(15, 185, 177, 0.2)', 
                            'rgba(15, 185, 177, 0)'
                        ]),
                        borderColor: colors.getPrimaryColor(),
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    scales: {
                        x: this.chartManager.getAxisConfig(),
                        y: {
                            ...this.chartManager.getAxisConfig(),
                            ticks: {
                                ...this.chartManager.getAxisConfig().ticks,
                                callback: (value) => this.formatCurrency(value)
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        },
        
        renderTrendsCharts() {
            const { monthly_performance, category_momentum } = this.data;
            const colors = this.chartManager.themeManager;
            
            // Monthly Performance Chart
            this.chartManager.create('monthlyPerformanceChart', {
                type: 'bar',
                data: {
                    labels: monthly_performance?.labels || [],
                    datasets: [{
                        label: 'Revenue',
                        data: monthly_performance?.values || [],
                        backgroundColor: colors.getPrimaryColor(),
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    scales: {
                        x: this.chartManager.getAxisConfig(),
                        y: {
                            ...this.chartManager.getAxisConfig(),
                            ticks: {
                                ...this.chartManager.getAxisConfig().ticks,
                                callback: (value) => this.formatCurrency(value)
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            
            // Category Momentum Chart
            this.chartManager.create('categoryMomentumChart', {
                type: 'bar',
                data: {
                    labels: category_momentum?.labels || [],
                    datasets: [{
                        label: 'Growth %',
                        data: category_momentum?.values || [],
                        backgroundColor: (context) => {
                            const value = context.raw;
                            return value >= 0 ? 'rgba(15,185,177,0.75)' : 'rgba(239,68,68,0.65)';
                        },
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            ...this.chartManager.getAxisConfig(),
                            ticks: {
                                ...this.chartManager.getAxisConfig().ticks,
                                callback: (value) => value + '%'
                            }
                        },
                        y: this.chartManager.getAxisConfig()
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        },
        
        renderWarehouseCharts() {
            const { warehouse } = this.data;
            const chartData = warehouse?.chart || {};
            const colors = this.chartManager.themeManager;
            
            this.chartManager.create('warehouseComparisonChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels || [],
                    datasets: [
                        {
                            label: 'Capacity',
                            data: chartData.capacity || [],
                            backgroundColor: 'rgba(15, 185, 177, 0.12)',
                            borderColor: colors.getPrimaryColor(),
                            borderWidth: 1.5,
                            borderRadius: 4,
                            borderSkipped: false
                        },
                        {
                            label: 'Used',
                            data: chartData.used || [],
                            backgroundColor: colors.getPrimaryColor(),
                            borderRadius: 4,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    scales: {
                        x: this.chartManager.getAxisConfig(),
                        y: {
                            ...this.chartManager.getAxisConfig(),
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        
        refresh() {
            this.loadData();
        },
        
        // Utility functions
        createGradient(canvasId, colors) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return colors[0];
            
            const ctx = canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 200);
            gradient.addColorStop(0, colors[0]);
            gradient.addColorStop(1, colors[1]);
            return gradient;
        },
        
        formatCurrency(value) {
            const num = parseFloat(value) || 0;
            if (num >= 1000000) return '$' + (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return '$' + (num / 1000).toFixed(1) + 'K';
            return '$' + num.toFixed(2);
        },
        
        formatNumber(value) {
            const num = parseInt(value) || 0;
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toLocaleString();
        },
        
        formatKPIValue(key, value) {
            const currencyKeys = ['average_stock_value', 'total_sales_today', 
                                 'total_sales_month', 'total_sales_year'];
            return currencyKeys.includes(key) ? this.formatCurrency(value) : this.formatNumber(value);
        },
        
        calculateTrend(metric) {
            // Implement trend calculation based on historical data
            return null;
        },
        
        getTrendDirection(metric) {
            // Implement trend direction logic
            return 'up';
        },
        
        getUtilizationStatus(utilization) {
            if (utilization < 60) return 'optimal';
            if (utilization < 85) return 'moderate';
            return 'high';
        },
        
        getUtilizationLabel(utilization) {
            if (utilization < 60) return 'Optimal';
            if (utilization < 85) return 'Moderate';
            return 'High';
        },
        
        getStockStatusLabel(status) {
            const labels = {
                ok: 'In Stock',
                low: 'Low Stock',
                critical: 'Critical'
            };
            return labels[status] || status;
        }
    }));
});

