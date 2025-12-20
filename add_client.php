<?php
// Initialize the session
session_start();
require_once 'db_config.php';
require_once 'utils.php';

// Check if the user is logged in and is a reseller, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["is_reseller"])) {
    header("location: login.php");
    exit;
}

$reseller_id = $_SESSION["id"];
$client_cost = get_setting($pdo, 'client_cost');
if (is_null($client_cost)) {
    $client_cost = 0.00; // Default value if not set
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $expiration_date = $_POST["expiration_date"];

    // Fetch reseller's credits
    $stmt = $pdo->prepare("SELECT credits FROM users WHERE id = :id");
    $stmt->execute(['id' => $reseller_id]);
    $reseller = $stmt->fetch();

    if ($reseller['credits'] >= $client_cost) {
        try {
            $pdo->beginTransaction();

            // Deduct credits from reseller
            $new_credits = $reseller['credits'] - $client_cost;
            $stmt = $pdo->prepare("UPDATE users SET credits = :credits WHERE id = :id");
            $stmt->execute(['credits' => $new_credits, 'id' => $reseller_id]);

            // Create the new client
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, reseller_id, expiration_date) VALUES (:username, :password, :reseller_id, :expiration_date)");
            $stmt->execute(['username' => $username, 'password' => $hashed_password, 'reseller_id' => $reseller_id, 'expiration_date' => $expiration_date]);
            $client_id = $pdo->lastInsertId();

            // Record the sale
            $stmt = $pdo->prepare("INSERT INTO sales (reseller_id, client_id, amount) VALUES (:reseller_id, :client_id, :amount)");
            $stmt->execute(['reseller_id' => $reseller_id, 'client_id' => $client_id, 'amount' => $client_cost]);

            $pdo->commit();
            header("location: reseller_dashboard.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "You do not have enough credits to add a new client.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Client</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Client</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="add_client.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Client">
                <a href="reseller_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>