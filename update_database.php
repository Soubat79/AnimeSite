<?php
session_start();
require_once 'common/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/login.php');
    exit;
}

$success = '';
$error = '';

// Check if columns exist and add them if missing
$check_columns = [
    'homepage_locked' => "ALTER TABLE site_settings ADD COLUMN homepage_locked BOOLEAN DEFAULT TRUE AFTER password_enabled",
    'site_locked' => "ALTER TABLE site_settings ADD COLUMN site_locked BOOLEAN DEFAULT FALSE AFTER homepage_locked"
];

foreach ($check_columns as $column => $sql) {
    $check_result = $conn->query("SHOW COLUMNS FROM site_settings LIKE '$column'");
    if ($check_result->num_rows === 0) {
        if ($conn->query($sql)) {
            $success .= "✓ Added '$column' column to site_settings table<br>";
        } else {
            $error .= "✗ Failed to add '$column' column: " . $conn->error . "<br>";
        }
    } else {
        $success .= "✓ Column '$column' already exists<br>";
    }
}

// Check if users table exists and create if missing
$users_table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($users_table_check->num_rows === 0) {
    $create_users_table = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar_url VARCHAR(500) DEFAULT NULL,
        subscription_type ENUM('free', 'premium') DEFAULT 'free',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_users_table)) {
        $success .= "✓ Created users table<br>";
        
        // Insert demo user
        $demo_password = password_hash('password', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (name, email, password) VALUES ('Demo User', 'user@demo.com', '$demo_password')");
        $success .= "✓ Added demo user (user@demo.com / password)<br>";
    } else {
        $error .= "✗ Failed to create users table: " . $conn->error . "<br>";
    }
} else {
    $success .= "✓ Users table already exists<br>";
}

// Update existing site_settings record if needed
$settings_check = $conn->query("SELECT * FROM site_settings LIMIT 1");
if ($settings_check->num_rows > 0) {
    $conn->query("UPDATE site_settings SET homepage_locked = TRUE WHERE homepage_locked IS NULL");
    $success .= "✓ Updated existing site settings<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - Adept Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="text-center mb-6">
            <i class="fas fa-database text-4xl text-orange-500 mb-3"></i>
            <h1 class="text-2xl font-bold">Database Update</h1>
            <p class="text-gray-400">Updating database schema for enhanced security</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-4">
                <h3 class="font-bold mb-2">Errors:</h3>
                <?php echo nl2br($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-4 rounded mb-4">
                <h3 class="font-bold mb-2">Update Results:</h3>
                <?php echo nl2br($success); ?>
            </div>
        <?php endif; ?>

        <div class="bg-yellow-500 text-black p-4 rounded mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Note:</strong> This update adds new security features to your database.
        </div>

        <div class="space-y-4">
            <div class="bg-gray-700 p-4 rounded">
                <h3 class="font-semibold mb-2">Changes Being Made:</h3>
                <ul class="list-disc list-inside text-gray-300 space-y-1">
                    <li>Add 'homepage_locked' column to site_settings</li>
                    <li>Add 'site_locked' column to site_settings</li>
                    <li>Create users table for authentication</li>
                    <li>Add demo user account</li>
                    <li>Set homepage_locked to TRUE by default</li>
                </ul>
            </div>

            <?php if (empty($success) && empty($error)): ?>
                <form method="POST">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-lg transition duration-200">
                        Run Database Update
                    </button>
                </form>
            <?php else: ?>
                <div class="flex space-x-4">
                    <a href="admin/settings.php" class="flex-1 bg-green-500 hover:bg-green-600 text-white text-center font-semibold py-3 rounded-lg transition duration-200">
                        Go to Settings
                    </a>
                    <a href="index.php" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center font-semibold py-3 rounded-lg transition duration-200">
                        Go to Homepage
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>