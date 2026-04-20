@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="d-flex flex-column gap-4 animate-fadeUp">
    <div class="custom-card settings-hero">
        <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="text-muted mb-0">Control appearance, preferences, and system defaults from one modern dashboard. These settings are designed for a clean admin experience and future expansion.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge badge-success">Light / Dark support</span>
                <span class="badge badge-warning">Browser-persisted preferences</span>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <div class="col-xl-6">
            <div class="custom-card">
                <div class="d-flex align-items-center justify-content-between mb-3 gap-3">
                    <div>
                        <h2 class="h5 mb-1">Theme & appearance</h2>
                        <p class="text-muted small mb-0">Choose your visual style and accent colors for a faster, more comfortable workflow.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-theme-btn">Reset defaults</button>
                </div>

                <div class="settings-block">
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Interface mode</p>
                            <p class="text-muted small mb-2">Quickly switch between light and dark.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-dark-mode">
                            <label class="form-check-label" for="settings-dark-mode">Dark mode</label>
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Accent color</p>
                            <p class="text-muted small mb-2">Pick an accent that feels right for your brand.</p>
                        </div>
                        <div class="accent-options d-flex flex-wrap gap-2">
                            <button type="button" class="accent-chip btn btn-sm" data-accent="#0fb9b1">Teal</button>
                            <button type="button" class="accent-chip btn btn-sm" data-accent="#4facfe">Blue</button>
                            <button type="button" class="accent-chip btn btn-sm" data-accent="#f18f01">Amber</button>
                            <button type="button" class="accent-chip btn btn-sm" data-accent="#8d6e9f">Purple</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="custom-card">
                <h2 class="h5 mb-3">User preferences</h2>
                <div class="settings-block">
                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Auto-refresh inventory</p>
                            <p class="text-muted small mb-2">Keep the stock view refreshed in active sessions.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-auto-refresh">
                            <label class="form-check-label" for="settings-auto-refresh">Enabled</label>
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Compact tables</p>
                            <p class="text-muted small mb-2">Reduce row spacing for faster scanning.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-compact-tables">
                            <label class="form-check-label" for="settings-compact-tables">Enabled</label>
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label">Email notifications</p>
                            <p class="text-muted small mb-2">Receive alerts for critical inventory and order events.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-email-notifications">
                            <label class="form-check-label" for="settings-email-notifications">Enabled</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="custom-card">
                <h2 class="h5 mb-3">System settings</h2>
                <div class="settings-block">
                    <div class="form-group mb-4">
                        <label class="settings-label" for="settings-default-warehouse">Default warehouse</label>
                        <select class="form-select" id="settings-default-warehouse">
                            <option value="main">Main warehouse</option>
                            <option value="east">East hub</option>
                            <option value="central">Central storage</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="settings-label" for="settings-date-format">Date format</label>
                        <select class="form-select" id="settings-date-format">
                            <option value="d/m/Y">DD/MM/YYYY</option>
                            <option value="m/d/Y">MM/DD/YYYY</option>
                            <option value="Y-m-d">YYYY-MM-DD</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="settings-label" for="settings-dashboard-layout">Dashboard layout</label>
                        <select class="form-select" id="settings-dashboard-layout">
                            <option value="overview">Overview + stats</option>
                            <option value="compact">Compact inventory overview</option>
                            <option value="workbench">Operational workbench</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="custom-card card-alert">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-info-circle-fill text-primary" style="font-size: 1.75rem"></i>
                    <div>
                        <h2 class="h6 mb-1">Local preference storage</h2>
                        <p class="text-muted small mb-0">Your appearance and preference selections are stored in the browser. This makes the page fast and responsive while keeping the UI scalable for a later backend sync.</p>
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
        const darkModeToggle = document.getElementById('settings-dark-mode');
        const accentButtons = document.querySelectorAll('.accent-chip');
        const resetThemeButton = document.getElementById('reset-theme-btn');
        const autoRefresh = document.getElementById('settings-auto-refresh');
        const compactTables = document.getElementById('settings-compact-tables');
        const emailNotifications = document.getElementById('settings-email-notifications');
        const defaultWarehouse = document.getElementById('settings-default-warehouse');
        const dateFormat = document.getElementById('settings-date-format');
        const dashboardLayout = document.getElementById('settings-dashboard-layout');

        const storedTheme = localStorage.getItem('theme') || 'light';
        const storedAccent = localStorage.getItem('accent') || '#0fb9b1';
        const storedAutoRefresh = localStorage.getItem('autoRefresh') === 'true';
        const storedCompactTables = localStorage.getItem('compactTables') === 'true';
        const storedEmails = localStorage.getItem('emailNotifications') === 'true';
        const storedWarehouse = localStorage.getItem('defaultWarehouse') || 'main';
        const storedDateFormat = localStorage.getItem('dateFormat') || 'd/m/Y';
        const storedLayout = localStorage.getItem('dashboardLayout') || 'overview';

        const setDarkMode = mode => {
            document.documentElement.classList.toggle('dark', mode === 'dark');
            darkModeToggle.checked = mode === 'dark';
            localStorage.setItem('theme', mode);
        };

        const setAccent = color => {
            document.documentElement.style.setProperty('--accent', color);
            document.documentElement.style.setProperty('--accent-soft', color + '22');
            accentButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.accent === color));
            localStorage.setItem('accent', color);
        };

        setDarkMode(storedTheme);
        setAccent(storedAccent);
        autoRefresh.checked = storedAutoRefresh;
        compactTables.checked = storedCompactTables;
        emailNotifications.checked = storedEmails;
        defaultWarehouse.value = storedWarehouse;
        dateFormat.value = storedDateFormat;
        dashboardLayout.value = storedLayout;

        darkModeToggle.addEventListener('change', function() {
            setDarkMode(this.checked ? 'dark' : 'light');
        });

        accentButtons.forEach(button => {
            button.addEventListener('click', function() {
                setAccent(this.dataset.accent);
            });
        });

        [autoRefresh, compactTables, emailNotifications].forEach(input => {
            input.addEventListener('change', function() {
                localStorage.setItem(this.id.replace('settings-', ''), this.checked);
            });
        });

        [defaultWarehouse, dateFormat, dashboardLayout].forEach(select => {
            select.addEventListener('change', function() {
                localStorage.setItem(this.id.replace('settings-', ''), this.value);
            });
        });

        resetThemeButton.addEventListener('click', function() {
            setDarkMode('light');
            setAccent('#0fb9b1');
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
        });
    });
</script>
@endpush
