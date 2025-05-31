<?php
// Define navigation items based on user role
$current_page = basename($_SERVER['PHP_SELF']);
$current_module = isset($current_module) ? $current_module : '';

// Default navigation items (can be customized per module)
$default_nav_items = [
    
];

// Merge with module-specific items if provided
$nav_items = isset($module_nav_items) ? array_merge($default_nav_items, $module_nav_items) : $default_nav_items;
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Navigation</h3>
    </div>
    <ul class="sidebar-menu">
        <?php foreach ($nav_items as $file => $label): ?>
            <li>
                <a href="<?= $file ?>" class="<?= ($current_page == $file || $current_module == $file) ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
        
        <!-- Dashboard link -->
        <li class="sidebar-divider">
            <a href="<?= $dashboard_url ?? '/mypetakom/dashboard/student_dashboard.php' ?>" class="dashboard-link">
                ← Back to Main Dashboard
            </a>
        </li>
    </ul>
</div>