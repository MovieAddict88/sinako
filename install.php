<?php
// Include the database configuration file
require_once 'db_config.php';

try {
// Create app_updates table
$sql_app_updates = 'CREATE TABLE IF NOT EXISTS app_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_code VARCHAR(50) NOT NULL,
    version_name VARCHAR(50) NOT NULL,
    apk_path VARCHAR(255) NOT NULL,
    file_size BIGINT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)';
$pdo->exec($sql_app_updates);
echo "App updates table created successfully<br>";
    // Create the users table with role column
    $sql_users = 'CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        login_code VARCHAR(255) UNIQUE,
        device_id VARCHAR(255),
        banned BOOLEAN NOT NULL DEFAULT FALSE,
        role ENUM("admin","user") NOT NULL DEFAULT "user",
        daily_limit BIGINT UNSIGNED DEFAULT 0,
        data_usage BIGINT UNSIGNED DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    $pdo->exec($sql_users);
    echo "Users table created successfully<br>";

    // Create VPN sessions table
    $sql_vpn_sessions = 'CREATE TABLE IF NOT EXISTS vpn_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME,
        ip_address VARCHAR(255) NOT NULL,
        bytes_in BIGINT,
        bytes_out BIGINT,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )';
    $pdo->exec($sql_vpn_sessions);
    echo "VPN sessions table created successfully<br>";

    // Create the promos table
    $sql_promos = 'CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_name VARCHAR(255) NOT NULL,
        icon_promo_path VARCHAR(255) NOT NULL
    )';
    $pdo->exec($sql_promos);
    echo "Promos table created successfully<br>";

    // Create VPN profiles table
    $sql_vpn_profiles = 'CREATE TABLE IF NOT EXISTS vpn_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        ovpn_config TEXT NOT NULL,
        type ENUM("Premium","Freemium") NOT NULL DEFAULT "Premium",
        promo_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (promo_id) REFERENCES promos(id)
    )';
    $pdo->exec($sql_vpn_profiles);
    echo "VPN profiles table created successfully<br>";

    // Create configurations table
    $sql_configurations = 'CREATE TABLE IF NOT EXISTS configurations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        carrier VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        config_text TEXT NOT NULL,
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    $pdo->exec($sql_configurations);
    echo "Configurations table created successfully<br>";

    // Insert sample configurations
    $stmt = $pdo->query("SELECT COUNT(*) FROM configurations");
    if ($stmt->fetchColumn() == 0) {
        $sample_configs = [
            [
                'carrier' => 'Globe Telecom',
                'name' => 'GoSURF',
                'config_text' => '# Globe GoSURF Configuration
http-proxy-option AGENT "Mozilla/5.0 (Linux; Android 13)"
http-proxy-option VERSION 1.1
http-proxy-option CUSTOM-HEADER "Host: gosurf.globe.com.ph"
http-proxy-option CUSTOM-HEADER "X-Online-Host: globe.com.ph"
http-proxy-option CUSTOM-HEADER "X-Forward-Host: gosurf.globe.com.ph"
http-proxy-option CUSTOM-HEADER "Connection: Keep-Alive"

# Choose your proxy server
http-proxy 110.78.141.147 8080
# Alternative: http-proxy 203.177.135.129 80'
            ],
            [
                'carrier' => 'Smart Communications',
                'name' => 'Smart Basic',
                'config_text' => '# Smart Basic Configuration
http-proxy-option AGENT "Mozilla/5.0 (Linux; Android 14)"
http-proxy-option VERSION 1.1
http-proxy-option CUSTOM-HEADER "Host: internet.smart.com.ph"
http-proxy-option CUSTOM-HEADER "X-Online-Host: smart.com.ph"
http-proxy-option CUSTOM-HEADER "X-Forward-Host: smart.com.ph"
http-proxy-option CUSTOM-HEADER "Connection: Keep-Alive"

# Smart Proxy
http-proxy 10.102.61.1 8080
http-proxy-timeout 30'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO configurations (carrier, name, config_text) VALUES (:carrier, :name, :config_text)");
        foreach ($sample_configs as $config) {
            $stmt->execute($config);
        }
        echo "Sample configurations inserted successfully<br>";
    }

    // Add promo_id to users table if it doesn't exist
    $sql_check_promo_id_users = "SHOW COLUMNS FROM `users` LIKE 'promo_id'";
    $stmt_check_users = $pdo->prepare($sql_check_promo_id_users);
    $stmt_check_users->execute();
    if ($stmt_check_users->rowCount() == 0) {
        $sql_add_promo_id_to_users = 'ALTER TABLE users ADD COLUMN promo_id INT, ADD FOREIGN KEY (promo_id) REFERENCES promos(id)';
        $pdo->exec($sql_add_promo_id_to_users);
        echo "promo_id column added to users table successfully<br>";
    }

    // Add promo_id to vpn_profiles table if it doesn't exist
    $sql_check_promo_id_vpn_profiles = "SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'";
    $stmt_check_vpn_profiles = $pdo->prepare($sql_check_promo_id_vpn_profiles);
    $stmt_check_vpn_profiles->execute();
    if ($stmt_check_vpn_profiles->rowCount() == 0) {
        $sql_add_promo_id_to_vpn_profiles = 'ALTER TABLE vpn_profiles ADD COLUMN promo_id INT, ADD FOREIGN KEY (promo_id) REFERENCES promos(id)';
        $pdo->exec($sql_add_promo_id_to_vpn_profiles);
        echo "promo_id column added to vpn_profiles table successfully<br>";
    }

    // Check if the admin user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute(['username' => 'admin']);
    
    if ($stmt->rowCount() === 0) {
        // Admin user does not exist, so create it
        $admin_user = 'admin';
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);

        $insert_admin_sql = 'INSERT INTO users (username, password, role) VALUES (:username, :password, "admin")';
        $stmt = $pdo->prepare($insert_admin_sql);
        $stmt->execute([
            'username' => $admin_user,
            'password' => $admin_pass
        ]);
        echo 'Default admin user created successfully (username: admin, password: admin123).<br>';
    } else {
        echo 'Admin user already exists.<br>';
    }

    // Run database migrations
    echo "<br><strong>Starting database setup/migration...</strong><br>";

    // Create migrations table if it doesn't exist
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');

    // Get all executed migrations
    $stmt = $pdo->query('SELECT migration FROM migrations');
    $executed_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get all migration files from the migrations directory
    $migration_files = glob(__DIR__ . '/migrations/*.php');
    sort($migration_files);

    foreach ($migration_files as $file) {
        $migration_name = basename($file);
        if (!in_array($migration_name, $executed_migrations)) {
            echo "Running migration: $migration_name...<br>";
            // The migration file should define a variable $sql
            unset($sql); // Unset previous sql variable
            require $file; 
            
            if (isset($sql)) {
                if ($pdo->exec($sql) !== false) {
                    // Add to migrations table
                    $stmt = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
                    $stmt->execute(['migration' => $migration_name]);
                    echo "Migration $migration_name completed.<br>";
                } else {
                    $error_info = $pdo->errorInfo();
                    echo "<strong style='color: red;'>Migration $migration_name failed: " . htmlspecialchars($error_info[2]) . "</strong><br>";
                    // Stop further execution if a migration fails
                    break;
                }
            } else {
                echo "No \$sql variable found in $migration_name. Skipping.<br>";
            }
        }
    }

    echo "<br><strong>Database setup/migration completed successfully!</strong><br>";
    echo '<a href="login.php" style="display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;">Go to Login</a>';

} catch (PDOException $e) {
    die('ERROR: Could not execute sql statement. ' . $e->getMessage());
}



?>