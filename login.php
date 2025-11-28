<?php
require_once 'common/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_logged_in'])) {
    $redirect = $_GET['redirect'] ?? 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$mode = $_GET['mode'] ?? 'login'; // login or signup
$redirect = $_GET['redirect'] ?? 'index.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'login') {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $redirect = $_POST['redirect'] ?? 'index.php';
        
        $result = $conn->query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found';
        }
    } else { // signup
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $redirect = $_POST['redirect'] ?? 'index.php';
        
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Check if email already exists
            $check_result = $conn->query("SELECT id FROM users WHERE email = '$email'");
            
            if ($check_result->num_rows === 0) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
                
                if ($conn->query($sql)) {
                    $_SESSION['success'] = 'Account created successfully! Please login.';
                    header('Location: login.php?mode=login&redirect=' . urlencode($redirect));
                    exit;
                } else {
                    $error = 'Error creating account: ' . $conn->error;
                }
            } else {
                $error = 'Email already registered';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mode === 'login' ? 'Login' : 'Sign Up'; ?> - Adept Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'crunch-orange': '#F47521',
                        'dark-bg': '#121212',
                        'dark-card': '#1A1A1A',
                        'dark-hover': '#2A2A2A'
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
                <i class="fas fa-film text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold mb-2">
                <?php echo $mode === 'login' ? 'Welcome Back' : 'Join Adept Cinema'; ?>
            </h1>
            <p class="text-gray-400">
                <?php echo $mode === 'login' ? 'Sign in to your account' : 'Create your account'; ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-500 p-3 rounded-lg mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-500 p-3 rounded-lg mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Toggle Buttons -->
        <div class="flex bg-gray-800 rounded-lg p-1 mb-6">
            <a href="?mode=login" class="flex-1 text-center py-2 rounded-lg transition <?php echo $mode === 'login' ? 'bg-crunch-orange text-white' : 'text-gray-400'; ?>">
                Login
            </a>
            <a href="?mode=signup" class="flex-1 text-center py-2 rounded-lg transition <?php echo $mode === 'signup' ? 'bg-crunch-orange text-white' : 'text-gray-400'; ?>">
                Sign Up
            </a>
        </div>

        <form method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <div class="space-y-4">
                <?php if ($mode === 'signup'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Full Name</label>
                        <div class="relative">
                            <input type="text" name="name" required
                                   class="w-full bg-gray-800 border border-gray-600 rounded-lg py-3 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange transition">
                            <div class="absolute left-4 top-3 text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" required
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg py-3 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange transition">
                        <div class="absolute left-4 top-3 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg py-3 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange transition">
                        <div class="absolute left-4 top-3 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>

                <?php if ($mode === 'signup'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" required
                                   class="w-full bg-gray-800 border border-gray-600 rounded-lg py-3 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange transition">
                            <div class="absolute left-4 top-3 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="w-full bg-crunch-orange hover:bg-orange-600 text-white font-semibold py-3 rounded-lg transition duration-200">
                    <?php echo $mode === 'login' ? 'Sign In' : 'Create Account'; ?>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-gray-400 text-sm">
            <?php if ($mode === 'login'): ?>
                <p>Don't have an account? <a href="?mode=signup" class="text-crunch-orange hover:underline">Sign up</a></p>
            <?php else: ?>
                <p>Already have an account? <a href="?mode=login" class="text-crunch-orange hover:underline">Sign in</a></p>
            <?php endif; ?>
        </div>

        <!-- Demo Credentials -->
        <div class="mt-6 p-4 bg-gray-800 rounded-lg">
            <h4 class="font-semibold text-sm mb-2 text-gray-300">Demo Credentials:</h4>
            <div class="text-xs text-gray-400 space-y-1">
                <div>Email: <span class="text-crunch-orange">user@demo.com</span></div>
                <div>Password: <span class="text-crunch-orange">password</span></div>
            </div>
        </div>
    </div>
</body>
</html>