<?php
session_start();
require_once 'auth.php';

if ($_SESSION['role'] !== 'reseller') {
    header('Location: login.php');
    exit;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Welcome, Reseller!</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Your Dashboard</h3>
    </div>
    <div class="card-body">
        <p>This is your reseller dashboard. You can manage your users here.</p>
    </div>
</div>

<?php include 'footer.php'; ?>
