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

// Get reseller information
$reseller_id = $_SESSION['reseller_id'];

// Check existence of id parameter before processing further
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    // Get URL parameter
    $id =  trim($_GET['id']);

    try {
        // Verify client belongs to reseller
        $sql_check = 'SELECT COUNT(*) FROM reseller_clients WHERE client_id = :client_id AND reseller_id = :reseller_id';
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':client_id', $id, PDO::PARAM_INT);
        $stmt_check->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();

        if ($count == 1) {
            $pdo->beginTransaction();

            // Delete from reseller_clients table
            $sql = 'DELETE FROM reseller_clients WHERE reseller_id = :reseller_id AND client_id = :client_id';

            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
                $stmt->bindParam(':client_id', $id, PDO::PARAM_INT);

                // Attempt to execute the prepared statement
                $stmt->execute();

                // Close statement
                unset($stmt);
            }

            // Delete from users table
            $sql = 'DELETE FROM users WHERE id = :id';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            $stmt->execute();

            // Close statement
            unset($stmt);
        }

            $pdo->commit();

            // Redirect to client management page
            header('location: reseller_clients.php');
            exit;
        } else {
            // Client does not belong to reseller, redirect
            header('location: reseller_clients.php');
            exit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    // Close connection
    unset($pdo);
} else {
    // URL doesn't contain id parameter. Redirect to error page
    header('location: reseller_clients.php');
    exit();
}
