@extends('layouts.app')

@section('title', 'Settings')

@push('styles')
    @vite(['resources/css/features/settings.css'])
@endpush

@section('content')
<div class="page-shell settings-page">
    <section class="page-hero">
        <div>
            <p class="page-eyebrow">Preferences</p>
            <h1 class="page-title">Workspace settings</h1>
            <p class="page-subtitle">Control appearance, table density, alert behavior, and workspace defaults with the same teal design language used across purchasing.</p>
        </div>
        <div class="page-actions">
            <span class="badge badge-primary">Brand aligned</span>
            <span class="badge badge-success">Dark mode ready</span>
        </div>
    </section>

    <div class="settings-form-grid">
        <div class="section-stack">
            <div class="surface-card settings-panel">
                <div class="card-header-row">
                    <div>
                        <h2 class="card-title">Theme and appearance</h2>
                        <p class="card-subtitle">Keep the interface subtle, modern, and teal-led.</p>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-theme-btn">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </button>
                </div>

                <div class="settings-block">
                    <div class="settings-row">
                        <div>
                            <p class="settings-label mb-1">Interface mode</p>
                            <p class="text-muted small mb-0">Switch between the light workspace and the dark workspace.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-dark-mode" role="switch">
                            <label class="form-check-label" for="settings-dark-mode">
                                <span id="mode-label">Light</span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label mb-1">Accent color</p>
                            <p class="text-muted small mb-0">Use your brand teal across buttons, charts, and selected states.</p>
                        </div>
                        <div class="accent-options">
                            <button type="button" class="btn btn-outline-secondary btn-sm accent-chip" data-accent="#0D9488">Teal</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm accent-chip" data-accent="#0F766E">Deep Teal</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm accent-chip" data-accent="#14B8A6">Mint Teal</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface-card settings-panel">
                <div class="card-header-row">
                    <div>
                        <h2 class="card-title">User preferences</h2>
                        <p class="card-subtitle">Fine-tune the density and notification behavior of the workspace.</p>
                    </div>
                </div>

                <div class="settings-block">
                    <div class="settings-row">
                        <div>
                            <p class="settings-label mb-1">Auto-refresh inventory</p>
                            <p class="text-muted small mb-0">Keep inventory cards and dashboards refreshed while you work.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-auto-refresh" role="switch">
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label mb-1">Compact tables</p>
                            <p class="text-muted small mb-0">Reduce table row height for dense inventory workflows.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-compact-tables" role="switch">
                        </div>
                    </div>

                    <div class="settings-row">
                        <div>
                            <p class="settings-label mb-1">Email notifications</p>
                            <p class="text-muted small mb-0">Receive alerts for critical stock and operational events.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="settings-email-notifications" role="switch">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-stack">
            <div class="surface-card settings-panel">
                <div class="card-header-row">
                    <div>
                        <h2 class="card-title">System defaults</h2>
                        <p class="card-subtitle">Set the workspace defaults your team relies on most.</p>
                    </div>
                </div>

                <div class="settings-block">
                    <div class="form-group">
                        <label class="form-label" for="settings-default-warehouse">Default warehouse</label>
                        <select class="form-select settings-select" id="settings-default-warehouse">
                            <option value="main">Main warehouse</option>
                            <option value="east">East hub</option>
                            <option value="central">Central storage</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="settings-date-format">Date format</label>
                        <select class="form-select settings-select" id="settings-date-format">
                            <option value="d/m/Y">DD/MM/YYYY</option>
                            <option value="m/d/Y">MM/DD/YYYY</option>
                            <option value="Y-m-d">YYYY-MM-DD</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label" for="settings-dashboard-layout">Dashboard layout</label>
                        <select class="form-select settings-select" id="settings-dashboard-layout">
                            <option value="overview">Overview + stats</option>
                            <option value="compact">Compact inventory overview</option>
                            <option value="workbench">Operational workbench</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-note">
                <div class="settings-note-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div>
                    <h2 class="card-title mb-1">Local preference storage</h2>
                    <p class="text-muted mb-0">These settings are stored in your browser so changes apply instantly. This keeps the experience fast and avoids blocking workflows while still preserving your UI choices.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/features/settings.js'])
@endpush
