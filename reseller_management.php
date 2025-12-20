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
           u.first_name, u.address, u.contact_number, u.credits,
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
    <div class="card-body" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Credits</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                            <td><?php echo '₱' . number_format($user['credits'] ?? 0, 2); ?></td>
                            <td>
                                <div class="d-flex align-items-center" style="gap: 5px;">
                                    <?php if ($user['is_reseller']): ?>
                                        <a href="edit_reseller.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                        <a href="delete_reseller.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this reseller?');">Delete</a>
                                    <?php else: ?>
                                        <form action="toggle_reseller.php" method="post" class="m-0">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Make Reseller</button>
                                        </form>
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
