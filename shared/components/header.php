<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current page title (can be customized per page)
$page_title = isset($page_title) ? $page_title : 'MyPetakom';
?>

<div class="header">
    <div class="header-content">
        <h1><?= $page_title ?></h1>
        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-info">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
                <a href="<?= $logout_url ?? '/mypetakom/logout.php' ?>" class="logout-btn">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</div>