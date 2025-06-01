<?php
// sidebar.php
?>
<style>
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        position: fixed;
        top: 60px;
        left: 0;
        width: 250px;
        z-index: 998;
        transition: transform 0.3s ease-in-out;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 15px 20px;
        border-radius: 0;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    
    .sidebar .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-left: 3px solid #3498db;
        transform: translateX(5px);
        backdrop-filter: blur(10px);
    }
    
    .sidebar .nav-link.active {
        background: linear-gradient(90deg, #3498db, #2980b9);
        border-left: 3px solid #1abc9c;
        color: white;
        box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
    }
    
    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
        margin-right: 10px;
        font-size: 1.1rem;
    }
    
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        text-align: center;
    }
    
    .sidebar-header h6 {
        color: #fff;
        font-weight: 600;
        letter-spacing: 1px;
        margin: 0;
        font-size: 0.9rem;
        text-transform: uppercase;
    }
    
    .nav-section {
        padding: 10px 20px 5px;
        color: rgba(255,255,255,0.5);
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 1px;
        margin-top: 10px;
    }
    
   
    
    .sidebar-backdrop.show {
        display: block;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            z-index: 999;
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
    }
    
    /* Badge styles for menu items */
    .nav-badge {
        background-color: #e74c3c;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: auto;
    }
    
    .nav-badge.success {
        background-color: #27ae60;
    }
    
    .nav-badge.warning {
        background-color: #f39c12;
    }
    
    .nav-badge.info {
        background-color: #3498db;
    }
</style>

<!-- Sidebar Component -->
<nav class="sidebar" id="sidebar">

    <div class="position-sticky">
        <!-- Main Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin_dashboard.php" data-section="dashboard">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php" data-section="users">
                    Users Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="memberships.php" data-section="memberships">
                    Memberships
                    <?php if(isset($pending_memberships) && $pending_memberships > 0): ?>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="events.php" data-section="events">
                    Events
                </a>
            </li>
        </ul>
</nav>

<script>
    // Handle navigation link clicks
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't prevent default for actual page navigation
            // e.preventDefault();
            
            // Remove active class from all links
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
        });
    });

    // Listen for sidebar toggle events
    document.addEventListener('toggleSidebar', function() {
        const sidebar = document.getElementById('sidebar');
        
        if (sidebar.classList.contains('show')) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    });

    // Close sidebar when clicking backdrop
    document.getElementById('sidebarBackdrop').addEventListener('click', closeMobileSidebar);

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop mode - ensure sidebar is visible and backdrop is hidden
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });

    // Function to set active menu item
    function setActiveMenuItem(section) {
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        const targetLink = document.querySelector(`[data-section="${section}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }
    }

</script>