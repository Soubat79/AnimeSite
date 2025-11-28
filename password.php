<?php
require_once 'common/config.php';

if (isset($_SESSION['site_authenticated'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_password = $_POST['password'] ?? '';
    
    if (!empty($settings['site_password']) && password_verify($entered_password, $settings['site_password'])) {
        $_SESSION['site_authenticated'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid password';
    }
}

// If password protection is disabled, redirect to home
if (!$settings['password_enabled']) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Password - <?php echo $settings['app_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'crunch-orange': '#F47521',
                        'dark-bg': '#121212',
                        'dark-card': '#1A1A1A'
                    }
                }
            }
        }

        // Disable right-click and text selection
        document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
        document.addEventListener('selectstart', function(e) { e.preventDefault(); });
    </script>
</head>
<body class="bg-dark-bg text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-dark-card rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-crunch-orange rounded-full mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-lock text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold mb-2">Enter Password</h1>
            <p class="text-gray-400">This content is password protected</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-500 p-3 rounded-lg mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <div class="relative">
                    <input type="password" 
                           name="password" 
                           placeholder="Enter access password" 
                           required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg py-4 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange transition">
                    <div class="absolute left-4 top-4 text-gray-400">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-crunch-orange hover:bg-orange-600 text-white font-semibold py-4 rounded-lg transition duration-200">
                Unlock Content
            </button>
        </form>

        <div class="mt-6 text-center text-gray-400 text-sm">
            <p>Contact administrator for access</p>
        </div>
    </div>

    <script>
        // Focus on password input
        document.querySelector('input[name="password"]').focus();
    </script>
</body>
</html>