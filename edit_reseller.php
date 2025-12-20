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

// Define variables and initialize with empty values
$username = $first_name = $last_name = $address = $contact_number = $credits = '';
$username_err = $first_name_err = $last_name_err = $address_err = $contact_number_err = $credits_err = '';
$id = 0;

// Processing form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get hidden input value
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

    // Validate address
    if (empty(trim($_POST['address']))) {
        $address_err = 'Please enter an address.';
    } else {
        $address = trim($_POST['address']);
    }

    // Validate contact number
    if (empty(trim($_POST['contact_number']))) {
        $contact_number_err = 'Please enter a contact number.';
    } else {
        $contact_number = trim($_POST['contact_number']);
    }

    // Validate credits
    if (!is_numeric($_POST['credits'])) {
        $credits_err = 'Please enter a valid amount for credits.';
    } else {
        $credits = $_POST['credits'];
    }

    // Check input errors before updating in database
    if (empty($username_err) && empty($first_name_err) && empty($last_name_err) && empty($address_err) && empty($contact_number_err) && empty($credits_err)) {
        // Prepare an update statement
        $sql = 'UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, address = :address, contact_number = :contact_number, credits = :credits WHERE id = :id';

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':credits', $credits, PDO::PARAM_STR); // Stored as DECIMAL
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records updated successfully. Redirect to reseller management page
                header('location: reseller_management.php');
                exit();
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    }
} else {
    // Check existence of id parameter before processing further
    if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
        // Get URL parameter
        $id =  trim($_GET['id']);

        // Prepare a select statement
        $sql = 'SELECT * FROM users WHERE id = :id AND is_reseller = 1';
        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Retrieve individual field value
                    $username = $row['username'];
                    $first_name = $row['first_name'];
                    $last_name = $row['last_name'];
                    $address = $row['address'];
                    $contact_number = $row['contact_number'];
                    $credits = $row['credits'];
                } else {
                    // URL doesn't contain valid id for a reseller. Redirect
                    header('location: reseller_management.php');
                    exit();
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }

            // Close statement
            unset($stmt);
        }
    } else {
        // URL doesn't contain id parameter. Redirect
        header('location: reseller_management.php');
        exit();
    }
}

include 'header.php';
?>

<div class="page-header">
    <h2>Edit Reseller</h2>
    <div class="page-actions">
        <a class='btn btn-secondary' href='reseller_management.php'>
            <span class="material-icons">arrow_back</span>
            Back to Reseller Management
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Update Reseller Information</h3>
    </div>
    <div class="card-body">
        <div class="form-container">
            <p>Please edit the input values and submit to update the reseller record.</p>
            <form action='edit_reseller.php' method='post'>
                <div class='form-group'>
                    <label class="form-label">Username</label>
                    <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>'>
                    <span class='text-danger'><?php echo $username_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">First Name</label>
                    <input type='text' name='first_name' class='form-control' value='<?php echo htmlspecialchars($first_name); ?>'>
                    <span class='text-danger'><?php echo $first_name_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Last Name</label>
                    <input type='text' name='last_name' class='form-control' value='<?php echo htmlspecialchars($last_name); ?>'>
                    <span class='text-danger'><?php echo $last_name_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Address</label>
                    <textarea name='address' class='form-control'><?php echo htmlspecialchars($address); ?></textarea>
                    <span class='text-danger'><?php echo $address_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Contact Number</label>
                    <input type='text' name='contact_number' class='form-control' value='<?php echo htmlspecialchars($contact_number); ?>'>
                    <span class='text-danger'><?php echo $contact_number_err; ?></span>
                </div>
                 <div class='form-group'>
                    <label class="form-label">Credits</label>
                    <input type='number' step='0.01' name='credits' class='form-control' value='<?php echo htmlspecialchars($credits); ?>'>
                    <span class='text-danger'><?php echo $credits_err; ?></span>
                </div>
                <input type='hidden' name='id' value='<?php echo $id; ?>'/>
                <div class='form-group'>
                    <input type='submit' class='btn btn-primary' value='Update Reseller'>
                    <a class='btn btn-link' href='reseller_management.php'>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
