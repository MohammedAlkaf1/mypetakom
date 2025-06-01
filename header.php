<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(135deg, #004080 0%, #004080 100%);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
            height: 60px;
            backdrop-filter: blur(10px);
        }
        
        .header h5 {
            color: white;
            font-weight: 600;
            margin: 0;
        }
        
        .header .welcome-text {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }
        
        .header .btn-logout {
            background: rgba(255, 1, 1, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .header .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            color: white;
            transform: translateY(-1px);
        }
        
        .sidebar-toggle {
            display: none;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            
            .header .d-flex {
                flex-wrap: wrap;
            }
            
            .header .welcome-text {
                font-size: 0.8rem;
            }
            
            .header .btn-logout {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }
        
        .notification-badge {
            position: relative;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            border-radius: 50%;
            padding: 4px 6px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
    <!-- Header Component -->
    <header class="header d-flex justify-content-between align-items-center px-4">
        <div class="d-flex align-items-center">
            <!-- Mobile Sidebar Toggle -->
            <button class="sidebar-toggle me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div>
                <h5 class="mb-0">
                    MyPetakom
                </h5>
                
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            
            
            <!-- User Info -->
            <div class="d-flex align-items-center">
                <span class="welcome-text me-3">
                    Welcome, <strong><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Admin'; ?></strong>
                </span>

                <!-- Logout Button -->
                <a href="index.php" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            // Trigger sidebar toggle event
            const event = new CustomEvent('toggleSidebar');
            document.dispatchEvent(event);
        });

        // Function to update user name (can be called from other scripts)
        function updateUserName(name) {
            const userSpan = document.querySelector('.header .welcome-text strong');
            if (userSpan) {
                userSpan.textContent = name;
            }
        }

        // Function to update notification count
        function updateNotificationCount(count) {
            const badge = document.querySelector('.notification-badge .badge');
            if (count > 0) {
                if (badge) {
                    badge.textContent = count;
                } else {
                    const notificationIcon = document.querySelector('.notification-badge');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge';
                    newBadge.textContent = count;
                    notificationIcon.appendChild(newBadge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        }
    </script>
</body>
</html>