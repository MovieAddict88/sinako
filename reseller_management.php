<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, is_reseller FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include 'header.php';
?>

<div class="page-header">
    <h2>Reseller Management</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Users</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo $user['is_reseller'] ? 'Reseller' : 'User'; ?></td>
                        <td>
                            <form action="toggle_reseller.php" method="post" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="btn <?php echo $user['is_reseller'] ? 'btn-danger' : 'btn-success'; ?>">
                                    <?php echo $user['is_reseller'] ? 'Remove Reseller' : 'Make Reseller'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
