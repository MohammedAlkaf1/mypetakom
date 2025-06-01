// Smart prevent back button - only after logout
(function() {
    'use strict';
    
    var loginUrl = '/mypetakom/login.php'; // Default login URL
    
    // Function to check if user came from logout
    function checkLogoutContext() {
        // Check if we came from logout page
        var referrer = document.referrer || '';
        var cameFromLogout = referrer.indexOf('logout.php') !== -1;
        
        // Check if there's a logout flag in sessionStorage
        var logoutFlag = sessionStorage.getItem('user_logged_out');
        
        // Check navigation type (back button) AND logout context
        var isBackButton = window.performance && window.performance.navigation.type === 2;
        
        return isBackButton && (cameFromLogout || logoutFlag === 'true');
    }
    
    // Only redirect if coming from logout via back button
    if (checkLogoutContext()) {
        var bodyLoginUrl = document.body ? document.body.getAttribute('data-login-url') : null;
        var redirectUrl = bodyLoginUrl || loginUrl;
        window.location.replace(redirectUrl);
        return;
    }
    
    // Standard history manipulation for normal navigation
    if (window.history && window.history.pushState) {
        // Handle popstate (back button) during normal navigation
        window.addEventListener('popstate', function(event) {
            // Check if user is still logged in via a quick session check
            fetch('/mypetakom/shared/check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        // User is not logged in, redirect to login
                        var bodyLoginUrl = document.body ? document.body.getAttribute('data-login-url') : null;
                        var redirectUrl = bodyLoginUrl || loginUrl;
                        window.location.replace(redirectUrl);
                    }
                    // If logged in, allow normal back navigation
                })
                .catch(() => {
                    // If session check fails, redirect to login
                    var bodyLoginUrl = document.body ? document.body.getAttribute('data-login-url') : null;
                    var redirectUrl = bodyLoginUrl || loginUrl;
                    window.location.replace(redirectUrl);
                });
        });
    }
    
    // Clear logout flag when page loads normally (not via back button)
    if (window.performance && window.performance.navigation.type !== 2) {
        sessionStorage.removeItem('user_logged_out');
    }
    
})();