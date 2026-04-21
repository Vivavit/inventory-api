        // Initialize theme on page load
        function initializeTheme() {
            const html = document.documentElement;
            const theme = localStorage.getItem('theme') || 'light';
            const accent = localStorage.getItem('accent') || '#0fb9b1';

            // Set dark mode
            if (theme === 'dark') {
                html.classList.add('dark');
            }

            // Set accent color
            setAccentColor(accent);

            // Update theme toggle icon
            updateThemeIcon(theme === 'dark');
        }

        function setAccentColor(color) {
            const root = document.documentElement;
            root.style.setProperty('--color-accent', color);

            // Calculate RGB values for soft accent
            const rgb = hexToRgb(color);
            if (rgb) {
                root.style.setProperty('--color-accent-soft', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.14)`);
                root.style.setProperty('--color-accent-dark', `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.2)`);
            }
        }

        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        function updateThemeIcon(isDark) {
            const icon = document.getElementById('theme-toggle')?.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'bi bi-sun' : 'bi bi-moon';
            }
        }

        // Initialize before DOM renders
        initializeTheme();

        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const logoToggle = document.getElementById('logo-toggle');
            const hamburgerMenu = document.getElementById('hamburger-menu');
            const themeToggle = document.getElementById('theme-toggle');
            const searchInput = document.getElementById('search-input');
            const html = document.documentElement;

            // Sidebar toggle
            if (logoToggle) {
                logoToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebar-expanded', !sidebar.classList.contains('collapsed'));
                });
            }

            // Mobile hamburger menu
            if (hamburgerMenu) {
                hamburgerMenu.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });

                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !hamburgerMenu.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                });
            }

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        window.location.href = '/products?search=' + encodeURIComponent(this.value);
                    }
                });
            }

            // Theme toggle
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const isDark = html.classList.contains('dark');
                    html.classList.toggle('dark', !isDark);
                    localStorage.setItem('theme', !isDark ? 'dark' : 'light');
                    updateThemeIcon(!isDark);
                });
            }
        });
