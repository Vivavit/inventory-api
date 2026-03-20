/**
 * Sidebar Toggle Module
 * Handles sidebar expand/collapse with localStorage persistence
 */

export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');

    if (!sidebar || !toggleBtn) return;

    // Get saved state from localStorage, default to expanded
    const isSidebarCollapsed = localStorage.getItem('sidebar-expanded') === 'false';

    // Apply initial state
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        updateToggleButtonIcon(toggleBtn, true);
    } else {
        sidebar.classList.remove('collapsed');
        updateToggleButtonIcon(toggleBtn, false);
    }

    // Toggle sidebar on button click
    toggleBtn.addEventListener('click', function () {
        const isNowCollapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar-expanded', !isNowCollapsed);
        updateToggleButtonIcon(toggleBtn, isNowCollapsed);
    });
}

/**
 * Update toggle button icon based on sidebar state
 */
function updateToggleButtonIcon(button, isCollapsed) {
    button.innerHTML = isCollapsed
        ? '<i class="bi bi-chevron-right"></i>'
        : '<i class="bi bi-chevron-left"></i>';
}

/**
 * Close sidebar on mobile when a link is clicked
 */
export function closeSidebarOnMobile() {
    const sidebar = document.getElementById('sidebar');
    const links = sidebar?.querySelectorAll('a');

    if (!links) return;

    links.forEach(link => {
        link.addEventListener('click', function () {
            // Only auto-close on mobile screens
            if (window.innerWidth < 768) {
                sidebar.classList.remove('open');
            }
        });
    });
}

/**
 * Toggle sidebar on mobile hamburger menu
 */
export function enableMobileToggle() {
    // You can add a mobile hamburger menu here
    // For now, the sidebar toggle button works on all screen sizes
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    closeSidebarOnMobile();
    enableMobileToggle();
});
