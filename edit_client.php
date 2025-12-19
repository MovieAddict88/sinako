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

// Define variables and initialize with empty values
$username = $first_name = $last_name = $contact_number = '';
$username_err = $first_name_err = $last_name_err = $contact_number_err = '';

// Processing form data when form is submitted
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];

    // Validate username
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        $username = trim($_POST['username']);
    }

    // Validate first name
    if (empty(trim($_POST['first_name']))) {
        $first_name_err = 'Please enter a first name.';
    } else {
        $first_name = trim($_POST['first_name']);
    }

    // Validate last name
    if (empty(trim($_POST['last_name']))) {
        $last_name_err = 'Please enter a last name.';
    } else {
        $last_name = trim($_POST['last_name']);
    }

    // Validate contact number
    if (empty(trim($_POST['contact_number']))) {
        $contact_number_err = 'Please enter a contact number.';
    } else {
        $contact_number = trim($_POST['contact_number']);
    }

    // Check input errors before updating in database
    if (empty($username_err) && empty($first_name_err) && empty($last_name_err) && empty($contact_number_err)) {
        // Verify client belongs to reseller
        $sql_check = 'SELECT COUNT(*) FROM reseller_clients WHERE client_id = :client_id AND reseller_id = :reseller_id';
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':client_id', $id, PDO::PARAM_INT);
        $stmt_check->bindParam(':reseller_id', $reseller_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();

        if ($count == 1) {
            // Prepare an update statement
            $sql = 'UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, contact_number = :contact_number, daily_limit = :daily_limit WHERE id = :id';

            if ($stmt = $pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
                $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
                $stmt->bindParam(':daily_limit', $param_daily_limit, PDO::PARAM_INT);
                $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);

                // Set parameters
                $param_username = $username;
                $param_first_name = $first_name;
                $param_last_name = $last_name;
                $param_contact_number = $contact_number;
                $param_daily_limit = convert_to_bytes($_POST['limit_value'], $_POST['limit_unit']);
                $param_id = $id;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Records updated successfully. Redirect to landing page
                    header('location: reseller_clients.php');
                    exit();
                } else {
                    echo 'Something went wrong. Please try again later.';
                }

                // Close statement
                unset($stmt);
            }
        } else {
            // Client does not belong to reseller, redirect
            header('location: reseller_clients.php');
            exit();
        }
    }

    // Close connection
    unset($pdo);
} else {
    // Check existence of id parameter before processing further
    if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
        // Get URL parameter
        $id =  trim($_GET['id']);

        // Prepare a select statement
        $sql = 'SELECT u.* FROM users u JOIN reseller_clients rc ON u.id = rc.client_id WHERE u.id = :id AND rc.reseller_id = :reseller_id';
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':id', $param_id, PDO::PARAM_INT);
            $stmt->bindParam(':reseller_id', $param_reseller_id, PDO::PARAM_INT);

            // Set parameters
            $param_id = $id;
            $param_reseller_id = $reseller_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Retrieve individual field value
                    $username = $row['username'];
                    $first_name = $row['first_name'];
                    $last_name = $row['last_name'];
                    $contact_number = $row['contact_number'];
                    $daily_limit = $row['daily_limit'];

                    if ($daily_limit > 0) {
                        if ($daily_limit >= 1073741824) {
                            $limit_value = $daily_limit / 1073741824;
                            $limit_unit = 'GB';
                        } elseif ($daily_limit >= 1048576) {
                            $limit_value = $daily_limit / 1048576;
                            $limit_unit = 'MB';
                        } else {
                            $limit_value = $daily_limit / 1024;
                            $limit_unit = 'KB';
                        }
                    } else {
                        $limit_value = 0;
                        $limit_unit = 'MB';
                    }

                } else {
                    // URL doesn't contain valid id. Redirect to error page
                    header('location: reseller_clients.php');
                    exit();
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }

        // Close connection
        unset($pdo);
    } else {
        // URL doesn't contain id parameter. Redirect to error page
        header('location: reseller_clients.php');
        exit();
    }
}
include 'header.php';
?>

<div class="page-header">
    <h2>Edit Client</h2>
    <div class="page-actions">
        <a href="reseller_clients.php" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span>
            Back to Clients
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Update Client Information</h3>
    </div>
    <div class="card-body">
        <form action="edit_client.php" method="post">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($contact_number); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Daily Limit</label>
                <div class="input-group">
                    <input type="number" name="limit_value" class="form-control" placeholder="Enter limit" value="<?php echo $limit_value; ?>">
                    <select name="limit_unit" class="form-control">
                        <option value="KB" <?php if ($limit_unit == 'KB') echo 'selected'; ?>>KB</option>
                        <option value="MB" <?php if ($limit_unit == 'MB') echo 'selected'; ?>>MB</option>
                        <option value="GB" <?php if ($limit_unit == 'GB') echo 'selected'; ?>>GB</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Client">
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
