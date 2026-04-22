/**
 * Layout Features - Sidebar & Theme Management
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // THEME MANAGEMENT
    // ============================================
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle?.querySelector('i');
    
    // Get saved theme or default to light
    const getSavedTheme = () => {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) return savedTheme;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };
    
    // Apply theme to HTML element
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if (themeIcon) {
                themeIcon.classList.remove('bi-moon');
                themeIcon.classList.add('bi-sun');
            }
        } else {
            document.documentElement.classList.remove('dark');
            if (themeIcon) {
                themeIcon.classList.remove('bi-sun');
                themeIcon.classList.add('bi-moon');
            }
        }
        localStorage.setItem('theme', theme);
    };
    
    // Initialize theme
    if (themeToggle) {
        const savedTheme = getSavedTheme();
        applyTheme(savedTheme);
        
        // Toggle theme on click
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }
    
    // ============================================
    // SIDEBAR MANAGEMENT
    // ============================================
    const sidebar = document.getElementById('sidebar');
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const logoToggle = document.getElementById('logo-toggle');
    const topHeader = document.getElementById('top-header');
    const mainContent = document.getElementById('main-content');
    
    // Get saved sidebar state
    const getSavedSidebarState = () => {
        return localStorage.getItem('sidebarCollapsed') === 'true';
    };
    
    // Apply sidebar state
    const applySidebarState = (collapsed) => {
        if (!sidebar) return;
        
        if (collapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
        localStorage.setItem('sidebarCollapsed', collapsed);
    };
    
    // Initialize sidebar state
    const isCollapsed = getSavedSidebarState();
    applySidebarState(isCollapsed);
    
    // Toggle sidebar on logo click (for desktop)
    if (logoToggle) {
        logoToggle.addEventListener('click', () => {
            // Only toggle on desktop (not mobile)
            if (window.innerWidth > 768) {
                const currentlyCollapsed = sidebar.classList.contains('collapsed');
                applySidebarState(!currentlyCollapsed);
            }
        });
    }
    
    // Mobile menu toggle
    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            
            // Prevent body scroll when mobile menu is open
            if (sidebar.classList.contains('mobile-open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar?.classList.contains('mobile-open')) {
            if (!sidebar.contains(e.target) && !hamburgerMenu?.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        }
    });
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (window.innerWidth > 768) {
                // Remove mobile-open class when switching to desktop
                if (sidebar?.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    document.body.style.overflow = '';
                }
            }
        }, 250);
    });
    
    // ============================================
    // USER MENU DROPDOWN
    // ============================================
    const userMenuBtn = document.getElementById('user-menu-btn');
    
    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            // You can implement dropdown menu here
            console.log('User menu clicked');
        });
    }
    
    // ============================================
    // NOTIFICATIONS
    // ============================================
    const notificationsBtn = document.getElementById('notifications-btn');
    
    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', () => {
            // You can implement notifications panel here
            console.log('Notifications clicked');
        });
    }
    
    // ============================================
    // SEARCH FUNCTIONALITY
    // ============================================
    const searchInput = document.getElementById('search-input');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const searchTerm = e.target.value;
            
            searchTimeout = setTimeout(() => {
                if (searchTerm.length >= 2) {
                    // Implement search functionality
                    console.log('Searching for:', searchTerm);
                    // You can redirect to search results or filter content
                    // window.location.href = `/search?q=${encodeURIComponent(searchTerm)}`;
                }
            }, 500);
        });
        
        // Search on Enter key
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const searchTerm = e.target.value;
                if (searchTerm.trim()) {
                    window.location.href = `/search?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
    }
    
    // ============================================
    // ACTIVE LINK HIGHLIGHTING
    // ============================================
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar nav a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '#' && currentPath.includes(href)) {
            link.classList.add('active');
        }
        
        // Remove active class from other links in same group
        if (link.classList.contains('active')) {
            const parentGroup = link.closest('.nav-group');
            if (parentGroup) {
                const siblings = parentGroup.querySelectorAll('a');
                siblings.forEach(sibling => {
                    if (sibling !== link) {
                        sibling.classList.remove('active');
                    }
                });
            }
        }
    });
    
    // ============================================
    // TOOLTIP FUNCTIONALITY (Optional)
    // ============================================
    const addTooltips = () => {
        const links = document.querySelectorAll('.sidebar nav a');
        links.forEach(link => {
            const title = link.getAttribute('title');
            const linkText = link.querySelector('.link-text')?.innerText;
            
            if (title && linkText && window.innerWidth <= 768) {
                // For mobile, show full text
                link.setAttribute('title', title);
            }
        });
    };
    
    addTooltips();
    
    // ============================================
    // KEYBOARD NAVIGATION
    // ============================================
    document.addEventListener('keydown', (e) => {
        // Alt + S to toggle sidebar
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            if (window.innerWidth > 768) {
                const currentlyCollapsed = sidebar.classList.contains('collapsed');
                applySidebarState(!currentlyCollapsed);
            } else {
                sidebar.classList.toggle('mobile-open');
            }
        }
        
        // Alt + D for dark mode toggle
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            themeToggle?.click();
        }
        
        // / to focus search
        if (e.key === '/' && searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape to blur search
        if (e.key === 'Escape' && searchInput === document.activeElement) {
            searchInput.blur();
        }
    });
    
    // ============================================
    // LOGOUT CONFIRMATION (Optional)
    // ============================================
    const logoutLink = document.querySelector('a[href*="logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            const confirmed = confirm('Are you sure you want to logout?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }
    
    console.log('Layout initialized successfully');
});