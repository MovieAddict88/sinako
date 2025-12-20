<?php
// Process delete operation after confirmation
if (isset($_POST['id']) && !empty($_POST['id'])) {
    // Include config file
    require_once 'db_config.php';
    session_start();

    // Check if the user is logged in and is admin
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
        header('location: login.php');
        exit;
    }

    // Prepare a delete statement
    // We are not actually deleting the user, but marking them as not a reseller.
    // The user can be deleted from the main user management page.
    $sql = 'UPDATE users SET is_reseller = 0, reseller_id = NULL WHERE id = :id';

    if ($stmt = $pdo->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

        // Set parameters
        $param_id = trim($_POST['id']);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to landing page
            header('location: reseller_management.php');
            exit();
        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }
    }

    // Close statement
    unset($stmt);

    // Close connection
    unset($pdo);
} else {
    // Check existence of id parameter
    if (empty(trim($_GET['id']))) {
        // URL doesn't contain id parameter. Redirect to error page
        // For simplicity, we'll just redirect to the main page.
        header('location: reseller_management.php');
        exit();
    }
}

include 'header.php';
?>

<div class="page-header">
    <h2>Delete Reseller</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Confirm Deletion</h3>
    </div>
    <div class="card-body">
        <div class="form-container">
            <form action="delete_reseller.php" method="post">
                <div class="alert alert-danger">
                    <input type="hidden" name="id" value="<?php echo trim($_GET['id']); ?>"/>
                    <p>Are you sure you want to remove this user's reseller status? This action cannot be undone.</p>
                    <p>
                        <input type="submit" value="Yes, Remove" class="btn btn-danger">
                        <a href="reseller_management.php" class="btn btn-secondary">No, Cancel</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
