// Function to check login status and update navbar
function updateNavbar() {
    // Ensure elements exist before making the request
    const signupLink = document.getElementById('nav-signup');
    const loginLink = document.getElementById('nav-login');
    const logoutLink = document.getElementById('nav-logout');
    
    if (!signupLink || !loginLink || !logoutLink) {
        // Elements not found yet, try again after a short delay
        setTimeout(updateNavbar, 100);
        return;
    }
    
    fetch('backend/check_session.php', {
        credentials: 'same-origin',
        cache: 'no-cache'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.logged_in) {
                // User is logged in - hide signup and login, show logout
                if (signupLink) signupLink.style.display = 'none';
                if (loginLink) loginLink.style.display = 'none';
                if (logoutLink) logoutLink.style.display = 'inline-block';
            } else {
                // User is not logged in - show signup and login, hide logout
                if (signupLink) signupLink.style.display = 'inline-block';
                if (loginLink) loginLink.style.display = 'inline-block';
                if (logoutLink) logoutLink.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking session:', error);
            // On error, show default state (logged out)
            const signupLink = document.getElementById('nav-signup');
            const loginLink = document.getElementById('nav-login');
            const logoutLink = document.getElementById('nav-logout');
            
            if (signupLink) signupLink.style.display = 'inline-block';
            if (loginLink) loginLink.style.display = 'inline-block';
            if (logoutLink) logoutLink.style.display = 'none';
        });
}

// Update navbar when page loads
function initNavbar() {
    // Try multiple times to ensure it works even after redirects
    updateNavbar();
    
    // Also update after a short delay to catch any timing issues
    setTimeout(updateNavbar, 200);
    setTimeout(updateNavbar, 500);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavbar);
} else {
    // DOM is already loaded (including after redirects)
    initNavbar();
}

// Also listen for pageshow event (fires even when page is loaded from cache)
window.addEventListener('pageshow', function(event) {
    // Update navbar when page is shown (including back/forward navigation)
    updateNavbar();
});

