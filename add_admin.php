<?php
// Start session
session_start();

// Check if the user is logged in and is admin, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';
require_once 'utils.php';

// Define variables and initialize with empty values
$username = $password = $login_code = $first_name = $last_name = $contact_number = $address = '';
$credits = 0;
$username_err = $password_err = $login_code_err = $first_name_err = $last_name_err = $contact_number_err = $address_err = '';

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate username
    if (empty(trim($_POST['username']))) {
        $username_err = 'Please enter a username.';
    } else {
        // Prepare a select statement
        $sql = 'SELECT id FROM users WHERE username = :username';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = trim($_POST['username']);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = 'This username is already taken.';
                } else {
                    $username = trim($_POST['username']);
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST['password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password must have atleast 6 characters.';
    } else {
        $password = trim($_POST['password']);
    }

    $first_name = 'Admin';
    $last_name = 'User';
    $contact_number = '0';
    $address = '';

    $_POST['limit_value'] = 0;
    $_POST['limit_unit'] = 'MB';
    $_POST['promo_id'] = null;
    $_POST['billing_month'] = null;

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO users (username, password, first_name, last_name, contact_number, address, credits, login_code, role, daily_limit, promo_id, billing_month) VALUES (:username, :password, :first_name, :last_name, :contact_number, :address, :credits, :login_code, :role, :daily_limit, :promo_id, :billing_month)';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $param_password, PDO::PARAM_STR);
            $stmt->bindParam(':first_name', $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $param_contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':address', $param_address, PDO::PARAM_STR);
            $stmt->bindParam(':credits', $param_credits, PDO::PARAM_STR);
            $stmt->bindParam(':login_code', $param_login_code, PDO::PARAM_STR);
            $stmt->bindParam(':role', $param_role, PDO::PARAM_STR);
            $stmt->bindParam(':daily_limit', $param_daily_limit, PDO::PARAM_INT);
            $stmt->bindParam(':promo_id', $param_promo_id, PDO::PARAM_INT);
            $stmt->bindParam(':billing_month', $param_billing_month, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_contact_number = $contact_number;
            $param_address = $address;
            $param_credits = $credits;
            $param_login_code = generate_unique_login_code($pdo);
            $param_role = 'admin';
            $param_daily_limit = convert_to_bytes($_POST['limit_value'], $_POST['limit_unit']);
            $param_promo_id = !empty($_POST['promo_id']) ? $_POST['promo_id'] : null;
            $param_billing_month = !empty($_POST['billing_month']) ? $_POST['billing_month'] : null;

            // Attempt to execute the prepared statement
            try {
                if ($stmt->execute()) {
                    header('location: admin_management.php');
                    exit;
                } else {
                    echo 'Something went wrong. Please try again later.';
                }
            } catch (Exception $e) {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}

include 'header.php';
?>

<div class="page-header">
    <h2>Add New Admin</h2>
    <div class="page-actions">
        <a class='btn btn-secondary' href='admin_management.php'>
            <span class="material-icons">arrow_back</span>
            Back to Admin Management
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Admin Information</h3>
    </div>
    <div class="card-body">
        <div class="form-container">
            <p>Please fill this form to create a new admin user.</p>
            <form action='add_admin.php' method='post'>
                <div class='form-group'>
                    <label class="form-label">Username</label>
                    <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>'>
                    <span class='text-danger'><?php echo $username_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Password</label>
                    <input type='password' name='password' class='form-control'>
                    <span class='text-danger'><?php echo $password_err; ?></span>
                </div>
                <div class='form-group'>
                    <input type='submit' class='btn btn-primary' value='Create Admin'>
                    <a class='btn btn-link' href='admin_management.php'>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>