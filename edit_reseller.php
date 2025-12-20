<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$first_name = $last_name = $address = $contact_number = '';
$first_name_err = $last_name_err = $address_err = $contact_number_err = '';

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];

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

    if (empty($first_name_err) && empty($last_name_err) && empty($address_err) && empty($contact_number_err)) {
        $sql = 'UPDATE users SET first_name = :first_name, last_name = :last_name, address = :address, contact_number = :contact_number WHERE id = :id';

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('location: reseller_management.php');
                exit();
            } else {
                echo 'Something went wrong. Please try again later.';
            }
            unset($stmt);
        }
    }
    unset($pdo);
} else {
    if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
        $id =  trim($_GET['id']);
        $sql = 'SELECT * FROM users WHERE id = :id';
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $first_name = $row['first_name'];
                    $last_name = $row['last_name'];
                    $address = $row['address'];
                    $contact_number = $row['contact_number'];
                    $username = $row['username'];
                } else {
                    header('location: reseller_management.php');
                    exit();
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
            unset($stmt);
        }
    } else {
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
                    <input type='text' name='username' class='form-control' value='<?php echo htmlspecialchars($username); ?>' disabled>
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
                    <input type='text' name='address' class='form-control' value='<?php echo htmlspecialchars($address); ?>'>
                    <span class='text-danger'><?php echo $address_err; ?></span>
                </div>
                <div class='form-group'>
                    <label class="form-label">Contact Number</label>
                    <input type='text' name='contact_number' class='form-control' value='<?php echo htmlspecialchars($contact_number); ?>'>
                    <span class='text-danger'><?php echo $contact_number_err; ?></span>
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
