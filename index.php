<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Check if the user is a reseller
if (isset($_SESSION['is_reseller']) && $_SESSION['is_reseller'] == 1) {
    header('location: reseller_dashboard.php');
    exit;
}

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h1><?php echo translate('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3><?php echo translate('dashboard'); ?></h3>
    </div>
    <div class="card-body">
        <div class="dashboard-links">
            <a href="user_management.php" class="btn btn-primary">
                <span class="material-icons">people</span>
                <?php echo translate('user_management'); ?>
            </a>
            <a href="admin_management.php" class="btn btn-primary">
                <span class="material-icons">admin_panel_settings</span>
                <?php echo translate('admin_management'); ?>
            </a>
            <a href="reseller_management.php" class="btn btn-primary">
                <span class="material-icons">store</span>
                <?php echo translate('reseller_management'); ?>
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
