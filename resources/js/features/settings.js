document.addEventListener('DOMContentLoaded', () => {
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

    if (!darkModeToggle) {
        return;
    }

    const storedTheme = localStorage.getItem('theme') || 'light';
    const storedAccent = localStorage.getItem('accent') || '#0D9488';
    const storedAutoRefresh = localStorage.getItem('autoRefresh') === 'true';
    const storedCompactTables = localStorage.getItem('compactTables') === 'true';
    const storedEmails = localStorage.getItem('emailNotifications') === 'true';
    const storedWarehouse = localStorage.getItem('defaultWarehouse') || 'main';
    const storedDateFormat = localStorage.getItem('dateFormat') || 'd/m/Y';
    const storedLayout = localStorage.getItem('dashboardLayout') || 'overview';

    function hexToRgb(hex) {
        const normalized = hex.replace('#', '');
        const bigint = parseInt(normalized, 16);
        return {
            r: (bigint >> 16) & 255,
            g: (bigint >> 8) & 255,
            b: bigint & 255,
        };
    }

    function setDarkMode(isDark) {
        const html = document.documentElement;
        html.classList.toggle('dark', isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        darkModeToggle.checked = isDark;
        modeLabel.textContent = isDark ? 'Dark' : 'Light';

        const headerThemeIcon = document.getElementById('theme-toggle')?.querySelector('i');
        if (headerThemeIcon) {
            headerThemeIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
        }
    }

    function setAccentColor(color) {
        const root = document.documentElement;
        const { r, g, b } = hexToRgb(color);

        root.style.setProperty('--primary', color);
        root.style.setProperty('--primary-soft', `rgba(${r}, ${g}, ${b}, 0.12)`);
        root.style.setProperty('--primary-soft-hover', `rgba(${r}, ${g}, ${b}, 0.18)`);
        root.style.setProperty('--primary-ring', `rgba(${r}, ${g}, ${b}, 0.22)`);

        accentButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.accent.toLowerCase() === color.toLowerCase());
        });

        localStorage.setItem('accent', color);
    }

    function resetToDefaults() {
        setDarkMode(false);
        setAccentColor('#0D9488');

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
    }

    setDarkMode(storedTheme === 'dark');
    setAccentColor(storedAccent);
    autoRefresh.checked = storedAutoRefresh;
    compactTables.checked = storedCompactTables;
    emailNotifications.checked = storedEmails;
    defaultWarehouse.value = storedWarehouse;
    dateFormat.value = storedDateFormat;
    dashboardLayout.value = storedLayout;

    darkModeToggle.addEventListener('change', function () {
        setDarkMode(this.checked);
    });

    accentButtons.forEach((button) => {
        button.addEventListener('click', function () {
            setAccentColor(this.dataset.accent);
        });
    });

    [autoRefresh, compactTables, emailNotifications].forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            const keyMap = {
                'settings-auto-refresh': 'autoRefresh',
                'settings-compact-tables': 'compactTables',
                'settings-email-notifications': 'emailNotifications',
            };
            localStorage.setItem(keyMap[this.id], String(this.checked));
        });
    });

    [defaultWarehouse, dateFormat, dashboardLayout].forEach((select) => {
        select.addEventListener('change', function () {
            const keyMap = {
                'settings-default-warehouse': 'defaultWarehouse',
                'settings-date-format': 'dateFormat',
                'settings-dashboard-layout': 'dashboardLayout',
            };
            localStorage.setItem(keyMap[this.id], this.value);
        });
    });

    resetButton?.addEventListener('click', () => {
        if (confirm('Reset all settings to defaults?')) {
            resetToDefaults();
        }
    });
});
