<?php
require_once 'db_config.php';

function column_exists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column");
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (bool)$stmt->fetchColumn();
}

try {
    // Add is_reseller column
    if (!column_exists($pdo, 'users', 'is_reseller')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_reseller TINYINT(1) NOT NULL DEFAULT 0;");
        echo "Migration: is_reseller column added to users table.<br>";
    }

    // Add first_name column
    if (!column_exists($pdo, 'users', 'first_name')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NOT NULL;");
        echo "Migration: first_name column added to users table.<br>";
    }

    // Add last_name column
    if (!column_exists($pdo, 'users', 'last_name')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NOT NULL;");
        echo "Migration: last_name column added to users table.<br>";
    }

    // Add address column
    if (!column_exists($pdo, 'users', 'address')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT NOT NULL;");
        echo "Migration: address column added to users table.<br>";
    }

    // Add contact_number column
    if (!column_exists($pdo, 'users', 'contact_number')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN contact_number VARCHAR(20) NOT NULL;");
        echo "Migration: contact_number column added to users table.<br>";
    }

    // Add credits column
    if (!column_exists($pdo, 'users', 'credits')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN credits DECIMAL(10, 2) DEFAULT 0.00;");
        echo "Migration: credits column added to users table.<br>";
    }

    // Add reseller_id column
    if (!column_exists($pdo, 'users', 'reseller_id')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN reseller_id INT DEFAULT NULL;");
        echo "Migration: reseller_id column added to users table.<br>";
    }

    // Add expiration_date column
    if (!column_exists($pdo, 'users', 'expiration_date')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN expiration_date DATE DEFAULT NULL;");
        echo "Migration: expiration_date column added to users table.<br>";
    }

    // Add status column
    if (!column_exists($pdo, 'users', 'status')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active';");
        echo "Migration: status column added to users table.<br>";
    }

    // Add payment column
    if (!column_exists($pdo, 'users', 'payment')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN payment DECIMAL(10, 2) NOT NULL DEFAULT 0.00;");
        echo "Migration: payment column added to users table.<br>";
    }

    // Create sales table
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

    // Create resellers table
    $stmt = $pdo->query("SHOW TABLES LIKE 'resellers'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE resellers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );");
        echo "Migration: resellers table created.<br>";
    }
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
