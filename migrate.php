<?php
require_once 'db_config.php';

function column_exists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column");
    $stmt->execute(['table' => $table, 'column' => $column]);
    return $stmt->fetchColumn();
}

try {
    // Migration: 20240101_add_reseller_id_to_users.php
    if (!column_exists($pdo, 'users', 'reseller_id')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN reseller_id INT DEFAULT NULL;");
        echo "Migration: reseller_id column added to users table.<br>";
    }

    // Migration: 20240102_add_credits_to_users.php
    if (!column_exists($pdo, 'users', 'credits')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN credits DECIMAL(10, 2) DEFAULT 0.00;");
        echo "Migration: credits column added to users table.<br>";
    }

    // Migration: 20240103_add_expiration_date_to_users.php
    if (!column_exists($pdo, 'users', 'expiration_date')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN expiration_date DATE DEFAULT NULL;");
        echo "Migration: expiration_date column added to users table.<br>";
    }

    // Migration: 20240104_create_sales_table.php
    $stmt = $pdo->query("SHOW TABLES LIKE 'sales'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reseller_id INT NOT NULL,
            client_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reseller_id) REFERENCES users(id),
            FOREIGN KEY (client_id) REFERENCES users(id)
        );");
        echo "Migration: sales table created.<br>";
    }

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>