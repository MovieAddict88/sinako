<?php
require_once 'db_config.php';

try {
    // Create migrations table if it doesn't exist
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Get all executed migrations
    $stmt = $pdo->query('SELECT migration FROM migrations');
    $executed_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get all migration files
    $migration_files = glob('migrations/*.php');
    sort($migration_files);

    foreach ($migration_files as $file) {
        $migration_name = basename($file);
        if (!in_array($migration_name, $executed_migrations)) {
            echo "Running migration: $migration_name\n";
            require_once $file;
            if (isset($sql)) {
                $pdo->exec($sql);
            }

            // Add to migrations table
            $stmt = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
            $stmt->execute(['migration' => $migration_name]);
            echo "Migration $migration_name completed.\n";
        }
    }

    echo "All migrations have been run.\n";
} catch (PDOException $e) {
    die("ERROR: Could not run migrations. " . $e->getMessage());
}
?>
