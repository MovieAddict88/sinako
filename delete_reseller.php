<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // A non-technical error message is better for the user
    $_SESSION['error'] = "Invalid request.";
    header('Location: reseller_management.php');
    exit;
}

$user_id = $_POST['user_id'];

try {
    $pdo->beginTransaction();

    // Step 1: Delete all clients that belong to this reseller.
    // This is necessary as there is no FK constraint with cascading delete for this relationship.
    $stmt_delete_clients = $pdo->prepare("DELETE FROM users WHERE reseller_id = ?");
    $stmt_delete_clients->execute([$user_id]);

    // Step 2: Delete the reseller's own user record.
    // This will automatically trigger the ON DELETE CASCADE for the corresponding
    // record in the `resellers` table, as well as for related records in
    // `sales` and `commissions`.
    $stmt_delete_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt_delete_user->execute([$user_id]);

    $pdo->commit();

    // Redirect with a success message
    $_SESSION['success'] = "Reseller and all their clients have been successfully deleted.";
    header('Location: reseller_management.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    // Log the detailed technical error for the admin/developer
    error_log("Error deleting reseller: " . $e->getMessage());
    // Provide a user-friendly error message
    $_SESSION['error'] = "An error occurred while trying to delete the reseller. Please try again.";
    header('Location: reseller_management.php');
    exit;
}
