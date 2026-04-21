/**
 * resources/js/features/users.js
 */
document.addEventListener('DOMContentLoaded', () => {
    // Handle delete confirmations using event delegation
    const usersCard = document.querySelector('.users-card');
    
    if (usersCard) {
        usersCard.addEventListener('submit', (e) => {
            const form = e.target;
            
            // Check if the form being submitted is a delete form
            if (form.matches('form[action*="users"]') && form.querySelector('input[name="_method"][value="DELETE"]')) {
                const userName = form.dataset.userName || 'this user';
                if (!confirm(`Are you sure you want to delete user: ${userName}?`)) {
                    e.preventDefault(); // Stop form submission if canceled
                }
            }
        });
    }
});
