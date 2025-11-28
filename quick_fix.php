<?php
session_start();
require_once 'common/config.php';

// Quick fix for missing columns
$fix_queries = [
    "ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS homepage_locked BOOLEAN DEFAULT TRUE",
    "ALTER TABLE site_settings ADD COLUMN IF NOT EXISTS site_locked BOOLEAN DEFAULT FALSE",
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar_url VARCHAR(500) DEFAULT NULL,
        subscription_type ENUM('free', 'premium') DEFAULT 'free',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($fix_queries as $query) {
    $conn->query($query);
}

// Insert demo user if not exists
$demo_check = $conn->query("SELECT id FROM users WHERE email = 'user@demo.com'");
if ($demo_check->num_rows === 0) {
    $demo_password = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password) VALUES ('Demo User', 'user@demo.com', '$demo_password')");
}

echo "Database fixed successfully! <a href='admin/settings.php'>Go to Settings</a>";
?>