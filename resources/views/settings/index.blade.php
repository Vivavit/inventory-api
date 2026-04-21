@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="d-flex flex-column gap-4 animate-fadeUp">
    <!-- Header Card -->
    <div class="custom-card">
        <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="text-muted mb-0">Control appearance, preferences, and system defaults from one modern dashboard. These settings persist in your browser.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge badge-success">Light / Dark support</span>
                <span class="badge badge-warning">Auto-saved locally</span>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <!-- Left Column -->
        <div class="col-xl-6">
            <!-- Theme & Appearance -->
            <div class="custom-card">
                <div class="d-flex align-items-center justify-content-between mb-3 gap-3 flex-wrap">
                    <div>
                        <h2 class="h5 mb-1">Theme & appearance</h2>
                        <p class="text-muted small mb-0">Customize your visual experience with light/dark mode and accent colors.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-theme-btn">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                </div>

                <div class="settings-block">
                    <!-- Dark Mode Toggle -->
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Interface mode</p>
                            <p class="text-muted small mb-0">Switch between light and dark theme.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="settings-dark-mode"
                                role="switch"
                                aria-label="Toggle dark mode"
                            >
                            <label class="form-check-label" for="settings-dark-mode">
                                <span id="mode-label">Light</span>
                            </label>
                        </div>
                    </div>

                    <!-- Accent Color Selection -->
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Accent color</p>
                            <p class="text-muted small mb-0">Choose your preferred brand color.</p>
                        </div>
                        <div class="accent-options d-flex flex-wrap gap-2">
                            <button 
                                type="button" 
                                class="accent-chip btn btn-sm" 
                                data-accent="#0fb9b1"
                                title="Teal accent"
                                aria-label="Teal accent color"
                            >
                                Teal
                            </button>
                            <button 
                                type="button" 
                                class="accent-chip btn btn-sm" 
                                data-accent="#4facfe"
                                title="Blue accent"
                                aria-label="Blue accent color"
                            >
                                Blue
                            </button>
                            <button 
                                type="button" 
                                class="accent-chip btn btn-sm" 
                                data-accent="#f18f01"
                                title="Amber accent"
                                aria-label="Amber accent color"
                            >
                                Amber
                            </button>
                            <button 
                                type="button" 
                                class="accent-chip btn btn-sm" 
                                data-accent="#8d6e9f"
                                title="Purple accent"
                                aria-label="Purple accent color"
                            >
                                Purple
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Preferences -->
            <div class="custom-card">
                <h2 class="h5 mb-3">User preferences</h2>
                <div class="settings-block">
                    <!-- Auto-refresh -->
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Auto-refresh inventory</p>
                            <p class="text-muted small mb-0">Keep stock view updated in active sessions.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="settings-auto-refresh"
                                role="switch"
                                aria-label="Toggle auto-refresh"
                            >
                            <label class="form-check-label" for="settings-auto-refresh">
                                <span class="small">Enabled</span>
                            </label>
                        </div>
                    </div>

                    <!-- Compact Tables -->
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Compact tables</p>
                            <p class="text-muted small mb-0">Reduce row spacing for faster scanning.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="settings-compact-tables"
                                role="switch"
                                aria-label="Toggle compact tables"
                            >
                            <label class="form-check-label" for="settings-compact-tables">
                                <span class="small">Enabled</span>
                            </label>
                        </div>
                    </div>

                    <!-- Email Notifications -->
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Email notifications</p>
                            <p class="text-muted small mb-0">Get alerts for critical inventory events.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="settings-email-notifications"
                                role="switch"
                                aria-label="Toggle email notifications"
                            >
                            <label class="form-check-label" for="settings-email-notifications">
                                <span class="small">Enabled</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-xl-6">
            <!-- System Settings -->
            <div class="custom-card">
                <h2 class="h5 mb-3">System settings</h2>
                <div class="settings-block">
                    <!-- Default Warehouse -->
                    <div class="form-group">
                        <label class="settings-label" for="settings-default-warehouse">
                            <i class="bi bi-building me-2"></i>Default warehouse
                        </label>
                        <select class="form-select" id="settings-default-warehouse" aria-label="Select default warehouse">
                            <option value="main">Main warehouse</option>
                            <option value="east">East hub</option>
                            <option value="central">Central storage</option>
                        </select>
                    </div>

                    <!-- Date Format -->
                    <div class="form-group">
                        <label class="settings-label" for="settings-date-format">
                            <i class="bi bi-calendar-event me-2"></i>Date format
                        </label>
                        <select class="form-select" id="settings-date-format" aria-label="Select date format">
                            <option value="d/m/Y">DD/MM/YYYY</option>
                            <option value="m/d/Y">MM/DD/YYYY</option>
                            <option value="Y-m-d">YYYY-MM-DD</option>
                        </select>
                    </div>

                    <!-- Dashboard Layout -->
                    <div class="form-group">
                        <label class="settings-label" for="settings-dashboard-layout">
                            <i class="bi bi-layout-wtf me-2"></i>Dashboard layout
                        </label>
                        <select class="form-select" id="settings-dashboard-layout" aria-label="Select dashboard layout">
                            <option value="overview">Overview + stats</option>
                            <option value="compact">Compact inventory overview</option>
                            <option value="workbench">Operational workbench</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="custom-card card-alert">
                <div class="d-flex align-items-start gap-3">
                    <div style="flex-shrink: 0;">
                        <i class="bi bi-info-circle-fill" style="font-size: 1.5rem; color: var(--color-accent);"></i>
                    </div>
                    <div>
                        <h2 class="h6 mb-1">Local preference storage</h2>
                        <p class="text-muted small mb-0">Your settings are stored locally in your browser. This keeps everything responsive and instant. Future versions may support cloud sync.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Element references
        const darkModeToggle = document.getElementById('settings-dark-mode');
        const modeLabel = document.getElementById('mode-label');
        const accentButtons = document.querySelectorAll('.accent-chip');
        const resetButton = document.getElementById('reset-theme-btn');
        const autoRefresh = document.getElementById('settings-auto-refresh');
        const compactTables = document.getElementById('settings-compact-tables');
        const emailNotifications = document.getElementById('settings-email-notifications');
        const defaultWarehouse = document.getElementById('settings-default-warehouse');
        const dateFormat = document.getElementById('settings-date-format');
        const dashboardLayout = document.getElementById('settings-dashboard-layout');

        // Retrieve stored settings
        const storedTheme = localStorage.getItem('theme') || 'light';
        const storedAccent = localStorage.getItem('accent') || '#0fb9b1';
        const storedAutoRefresh = localStorage.getItem('autoRefresh') === 'true';
        const storedCompactTables = localStorage.getItem('compactTables') === 'true';
        const storedEmails = localStorage.getItem('emailNotifications') === 'true';
        const storedWarehouse = localStorage.getItem('defaultWarehouse') || 'main';
        const storedDateFormat = localStorage.getItem('dateFormat') || 'd/m/Y';
        const storedLayout = localStorage.getItem('dashboardLayout') || 'overview';

        /**
         * Helper: Convert hex color to RGB
         */
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        /**
         * Set dark mode and update UI
         */
        function setDarkMode(isDark) {
            const html = document.documentElement;
            const mode = isDark ? 'dark' : 'light';

            html.classList.toggle('dark', isDark);
            darkModeToggle.checked = isDark;
            modeLabel.textContent = isDark ? 'Dark' : 'Light';
            localStorage.setItem('theme', mode);

            // Update theme toggle icon in header
            const headerThemeIcon = document.getElementById('theme-toggle')?.querySelector('i');
            if (headerThemeIcon) {
                headerThemeIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
            }
        }

        /**
         * Set accent color with RGB calculation
         */
        function setAccentColor(color) {
            const root = document.documentElement;
            root.style.setProperty('--color-accent', color);

            const rgb = hexToRgb(color);
            if (rgb) {
                root.style.setProperty('--color-accent-soft', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.14)`);
                root.style.setProperty('--color-accent-dark', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.2)`);
            }

            // Update active button
            accentButtons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.accent === color);
            });

            localStorage.setItem('accent', color);
        }

        /**
         * Reset all settings to defaults
         */
        function resetToDefaults() {
            setDarkMode(false);
            setAccentColor('#0fb9b1');

            autoRefresh.checked = true;
            compactTables.checked = false;
            emailNotifications.checked = true;
            defaultWarehouse.value = 'main';
            dateFormat.value = 'd/m/Y';
            dashboardLayout.value = 'overview';

            localStorage.setItem('autoRefresh', 'true');
            localStorage.setItem('compactTables', 'false');
            localStorage.setItem('emailNotifications', 'true');
            localStorage.setItem('defaultWarehouse', 'main');
            localStorage.setItem('dateFormat', 'd/m/Y');
            localStorage.setItem('dashboardLayout', 'overview');

            // Provide feedback
            showNotification('Settings reset to defaults', 'success');
        }

        /**
         * Show temporary notification
         */
        function showNotification(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = message;
            alert.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                z-index: 1000;
                animation: slideInRight 0.3s ease-out;
                min-width: 250px;
            `;
            document.body.appendChild(alert);

            setTimeout(() => {
                alert.style.animation = 'slideInRight 0.3s ease-out reverse';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Initialize UI with stored settings
        setDarkMode(storedTheme === 'dark');
        setAccentColor(storedAccent);

        autoRefresh.checked = storedAutoRefresh;
        compactTables.checked = storedCompactTables;
        emailNotifications.checked = storedEmails;
        defaultWarehouse.value = storedWarehouse;
        dateFormat.value = storedDateFormat;
        dashboardLayout.value = storedLayout;

        // Event listeners for Dark Mode Toggle
        darkModeToggle.addEventListener('change', function() {
            setDarkMode(this.checked);
        });

        // Event listeners for Accent Color Buttons
        accentButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                setAccentColor(this.dataset.accent);
            });
        });

        // Event listeners for Checkbox Settings
        [autoRefresh, compactTables, emailNotifications].forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const key = this.id.replace('settings-', '');
                localStorage.setItem(key, this.checked);
            });
        });

        // Event listeners for Select Settings
        [defaultWarehouse, dateFormat, dashboardLayout].forEach(select => {
            select.addEventListener('change', function() {
                const key = this.id.replace('settings-', '');
                localStorage.setItem(key, this.value);
            });
        });

        // Reset Button
        resetButton.addEventListener('click', function() {
            if (confirm('Reset all settings to defaults? This cannot be undone.')) {
                resetToDefaults();
            }
        });
    });
</script>
@endpush