<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = '127.0.0.1';
    $user = 'root';
    $password = 'root';
    $dbname = 'adept_cinema_db';
    
    try {
        // Create connection
        $conn = new mysqli($host, $user, $password);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($conn->query($sql) === TRUE) {
            $success .= "Database created successfully<br>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Select database
        $conn->select_db($dbname);
        
        // Create tables
        $tables = [
            "CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                avatar_url VARCHAR(500) DEFAULT NULL,
                subscription_type ENUM('free', 'premium') DEFAULT 'free',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS movies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                poster_url VARCHAR(500) NOT NULL,
                description TEXT,
                rating DECIMAL(3,1),
                release_year INT,
                category_id INT,
                watch_link VARCHAR(500),
                episode_list TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                banner_image_url VARCHAR(500) NOT NULL,
                target_movie_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (target_movie_id) REFERENCES movies(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                app_name VARCHAR(100) DEFAULT 'Adept Cinema',
                site_password VARCHAR(255),
                password_enabled BOOLEAN DEFAULT FALSE,
                homepage_locked BOOLEAN DEFAULT TRUE,
                site_locked BOOLEAN DEFAULT FALSE,
                crunch_ui_enabled BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_views INT DEFAULT 0,
                unique_visitors INT DEFAULT 0,
                popular_content VARCHAR(255),
                visitor_ip VARCHAR(45),
                user_agent TEXT,
                visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $sql) {
            if ($conn->query($sql) !== TRUE) {
                throw new Exception("Error creating table: " . $conn->error);
            }
        }
        
        // Insert default admin
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT IGNORE INTO admin (username, password) VALUES ('admin', '$hashed_password')");
        
        // Insert default site settings - HOMEPAGE LOCKED BY DEFAULT
        $conn->query("INSERT IGNORE INTO site_settings (app_name, homepage_locked) VALUES ('Adept Cinema', TRUE)");
        
        // Insert default categories
        $default_categories = ['Action', 'Drama', 'Comedy', 'Horror', 'Sci-Fi', 'Romance'];
        foreach ($default_categories as $category) {
            $conn->query("INSERT IGNORE INTO categories (category_name) VALUES ('$category')");
        }
        
        // Insert sample user
        $user_password = password_hash('password', PASSWORD_DEFAULT);
        $conn->query("INSERT IGNORE INTO users (name, email, password) VALUES 
            ('Demo User', 'user@demo.com', '$user_password')");
        
        $success .= "All tables created successfully!<br>";
        $success .= "Default admin created: username: 'admin', password: 'admin123'<br>";
        $success .= "Demo user created: email: 'user@demo.com', password: 'password'<br>";
        $success .= "<strong>Homepage is locked by default - users must login to access content</strong><br>";
        
        // Create config file
        $config_content = "<?php
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

\$host = '127.0.0.1';
\$user = 'root';
\$password = 'root';
\$dbname = 'adept_cinema_db';

try {
    \$conn = new mysqli(\$host, \$user, \$password, \$dbname);
    if (\$conn->connect_error) {
        throw new Exception('Connection failed: ' . \$conn->connect_error);
    }
    
    // Set charset to utf8mb4
    \$conn->set_charset(\"utf8mb4\");
    
} catch (Exception \$e) {
    // Log error instead of displaying
    error_log('Database connection failed: ' . \$e->getMessage());
    die('Database connection failed. Please check the installation.');
}

// Get site settings
\$settings_result = \$conn->query('SELECT * FROM site_settings LIMIT 1');
if (\$settings_result) {
    \$settings = \$settings_result->fetch_assoc();
} else {
    // Default settings if table doesn't exist
    \$settings = [
        'app_name' => 'Adept Cinema',
        'password_enabled' => false,
        'homepage_locked' => true,
        'site_locked' => false,
        'crunch_ui_enabled' => true
    ];
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Enhanced Security Checks
function checkHomepageAccess() {
    global \$conn, \$settings;
    
    \$current_page = basename(\$_SERVER['PHP_SELF']);
    \$allowed_pages = ['login.php', 'password.php', 'install.php', '.create_htaccess.php'];
    
    // If site is locked and user is not logged in, redirect to login
    if (\$settings['site_locked'] && !isset(\$_SESSION['user_logged_in']) && !in_array(\$current_page, \$allowed_pages)) {
        header('Location: login.php?redirect=' . urlencode(\$_SERVER['REQUEST_URI']));
        exit;
    }
    
    // If homepage is locked and user is not logged in, and trying to access index.php, redirect to login
    if (\$settings['homepage_locked'] && !isset(\$_SESSION['user_logged_in']) && \$current_page === 'index.php') {
        header('Location: login.php?redirect=' . urlencode(\$_SERVER['REQUEST_URI']));
        exit;
    }
}

// Check site password protection
if (\$settings['password_enabled'] && basename(\$_SERVER['PHP_SELF']) !== 'password.php' && !isset(\$_SESSION['site_authenticated'])) {
    header('Location: password.php');
    exit;
}

// Run enhanced security check
checkHomepageAccess();

// Track page view (only if not admin area and not locked pages)
if (strpos(\$_SERVER['REQUEST_URI'], '/admin/') === false && !\$settings['site_locked']) {
    \$visitor_ip = \$_SERVER['REMOTE_ADDR'];
    \$user_agent = \$_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    \$current_page = \$_SERVER['REQUEST_URI'];
    
    // Sanitize inputs
    \$visitor_ip = \$conn->real_escape_string(\$visitor_ip);
    \$user_agent = \$conn->real_escape_string(substr(\$user_agent, 0, 500));
    
    // Check if this is a unique visitor today
    \$today = date('Y-m-d');
    \$unique_check = \$conn->query(\"SELECT id FROM analytics WHERE visitor_ip='\$visitor_ip' AND DATE(visited_at)='\$today' LIMIT 1\");
    
    if (\$unique_check->num_rows == 0) {
        \$conn->query(\"INSERT INTO analytics (unique_visitors, visitor_ip, user_agent) VALUES (1, '\$visitor_ip', '\$user_agent')\");
    }
    
    // Increment page views
    \$page_views_result = \$conn->query(\"SELECT id FROM analytics WHERE DATE(visited_at)='\$today' LIMIT 1\");
    if (\$page_views_result->num_rows > 0) {
        \$conn->query(\"UPDATE analytics SET page_views = page_views + 1 WHERE DATE(visited_at)='\$today'\");
    } else {
        \$conn->query(\"INSERT INTO analytics (page_views, visitor_ip, user_agent) VALUES (1, '\$visitor_ip', '\$user_agent')\");
    }
}

// CSRF Protection function
function generate_csrf_token() {
    if (empty(\$_SESSION['csrf_token'])) {
        \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return \$_SESSION['csrf_token'];
}

// Input sanitization function
function sanitize_input(\$data) {
    global \$conn;
    \$data = trim(\$data);
    \$data = stripslashes(\$data);
    \$data = htmlspecialchars(\$data);
    return \$conn->real_escape_string(\$data);
}
?>";
        
        file_put_contents('common/config.php', $config_content);
        $success .= "Config file created successfully!<br>";
        
        $_SESSION['install_success'] = $success;
        header('Location: admin/login.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Adept Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="text-center mb-6">
            <i class="fas fa-film text-4xl text-orange-500 mb-3"></i>
            <h1 class="text-2xl font-bold">Install Adept Cinema</h1>
            <p class="text-gray-400">Set up your movie database</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-3 rounded mb-4">
                <?php echo nl2br($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="bg-gray-700 p-4 rounded">
                <h3 class="font-semibold mb-2">Database Configuration</h3>
                <p class="text-sm text-gray-400">
                    Host: 127.0.0.1<br>
                    User: root<br>
                    Password: root<br>
                    Database: adept_cinema_db
                </p>
            </div>

            <div class="bg-yellow-500 text-black p-3 rounded text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Security Note:</strong> Homepage will be locked by default. Users must login to access content.
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 rounded-lg transition duration-200">
                Install Now
            </button>
        </form>
    </div>
</body>
</html>