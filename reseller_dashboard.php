<?php
// Start session
session_start();

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_reseller']) || $_SESSION['is_reseller'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Get reseller information
$reseller_id = $_SESSION['reseller_id'];

// Get client count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reseller_clients WHERE reseller_id = :reseller_id");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$client_count = $stmt->fetchColumn();

// Get total commission
$stmt = $pdo->prepare("SELECT SUM(commission_earned) FROM commissions WHERE reseller_id = :reseller_id");
$stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
$stmt->execute();
$total_commission = $stmt->fetchColumn();
if ($total_commission === null) {
    $total_commission = 0;
}

include 'header.php';
?>

<div class="page-header">
    <h2>Reseller Dashboard</h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Clients</h3>
            </div>
            <div class="card-body">
                <p>You have <?php echo $client_count; ?> clients.</p>
                <a href="reseller_clients.php" class="btn btn-primary">Manage Clients</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Commissions</h3>
            </div>
            <div class="card-body">
                <p>Your total commission is $<?php echo number_format($total_commission, 2); ?>.</p>
                <a href="reseller_commissions.php" class="btn btn-primary">View Commissions</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Branding</h3>
            </div>
            <div class="card-body">
                <p>Customize your client's experience.</p>
                <a href="reseller_branding.php" class="btn btn-primary">Manage Branding</a>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>Reports</h3>
            </div>
            <div class="card-body">
                <p>View detailed reports.</p>
                <a href="reseller_reports.php" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>
