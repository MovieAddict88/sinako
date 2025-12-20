<?php
session_start();
require_once 'auth.php';
require_once 'db_config.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.is_reseller, r.id as reseller_id,
           (SELECT COUNT(*) FROM users c WHERE c.reseller_id = u.id) as client_count,
           (SELECT SUM(c.commission_earned) FROM commissions c WHERE c.reseller_id = r.id) as total_commission
    FROM users u
    LEFT JOIN resellers r ON u.id = r.user_id
");
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
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Clients</th>
                        <th>Commission</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo $user['is_reseller'] ? 'Reseller' : 'User'; ?></td>
                            <td><?php echo $user['is_reseller'] ? $user['client_count'] : 'N/A'; ?></td>
                            <td><?php echo $user['is_reseller'] ? '₱' . number_format($user['total_commission'] ?? 0, 2) : 'N/A'; ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <form action="toggle_reseller.php" method="post" class="mb-1">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-block <?php echo $user['is_reseller'] ? 'btn-danger' : 'btn-success'; ?>">
                                            <?php echo $user['is_reseller'] ? 'Remove Reseller' : 'Make Reseller'; ?>
                                        </button>
                                    </form>
                                    <?php if ($user['is_reseller']): ?>
                                        <a href="view_reseller.php?id=<?php echo $user['reseller_id']; ?>" class="btn btn-secondary btn-block mb-1">View</a>
                                        <a href="reseller_dashboard.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-block">Dashboard</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
