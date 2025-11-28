<?php
require_once 'common/config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Get user's watch history
$history_result = $conn->query("
    SELECT m.* FROM movies m 
    ORDER BY m.created_at DESC 
    LIMIT 10
");
?>

<?php include 'common/header.php'; ?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-dark-card rounded-lg p-6 mb-6">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-crunch-orange rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-2xl text-white"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold mb-2"><?php echo $user['name']; ?></h1>
                    <p class="text-gray-400"><?php echo $user['email']; ?></p>
                    <div class="flex items-center space-x-4 mt-3">
                        <span class="bg-crunch-orange px-3 py-1 rounded-full text-sm">
                            <?php echo ucfirst($user['subscription_type']); ?> Member
                        </span>
                        <span class="text-gray-400 text-sm">
                            Joined <?php echo date('F Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>
                </div>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition">
                    Logout
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Watch Stats -->
            <div class="bg-dark-card rounded-lg p-6 text-center">
                <div class="text-crunch-orange text-2xl mb-2">
                    <i class="fas fa-play-circle"></i>
                </div>
                <h3 class="font-semibold">Continue Watching</h3>
                <p class="text-gray-400 text-sm">5 shows</p>
            </div>

            <div class="bg-dark-card rounded-lg p-6 text-center">
                <div class="text-crunch-orange text-2xl mb-2">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h3 class="font-semibold">Watchlist</h3>
                <p class="text-gray-400 text-sm">12 items</p>
            </div>

            <div class="bg-dark-card rounded-lg p-6 text-center">
                <div class="text-crunch-orange text-2xl mb-2">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="font-semibold">Watch Time</h3>
                <p class="text-gray-400 text-sm">24h 36m</p>
            </div>
        </div>

        <!-- Continue Watching -->
        <div class="bg-dark-card rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Continue Watching</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php while($movie = $history_result->fetch_assoc()): ?>
                    <a href="movie_details.php?id=<?php echo $movie['id']; ?>" class="block transform hover:scale-105 transition duration-300">
                        <div class="bg-gray-800 rounded-lg overflow-hidden relative">
                            <img src="<?php echo $movie['poster_url']; ?>" 
                                 alt="<?php echo $movie['title']; ?>" 
                                 class="w-full h-40 object-cover"
                                 onerror="this.src='https://via.placeholder.com/300x400/1A1A1A/FFFFFF?text=No+Image'">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                                <h3 class="font-semibold text-sm truncate"><?php echo $movie['title']; ?></h3>
                                <div class="w-full bg-gray-600 rounded-full h-1 mt-2">
                                    <div class="bg-crunch-orange h-1 rounded-full" style="width: 65%"></div>
                                </div>
                                <p class="text-gray-400 text-xs mt-1">65% watched</p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="bg-dark-card rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Account Settings</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-800 rounded-lg">
                    <div>
                        <h3 class="font-semibold">Subscription Plan</h3>
                        <p class="text-gray-400 text-sm">Current plan: <?php echo ucfirst($user['subscription_type']); ?></p>
                    </div>
                    <button class="bg-crunch-orange hover:bg-orange-600 text-white px-4 py-2 rounded transition">
                        Upgrade to Premium
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-800 rounded-lg">
                        <h3 class="font-semibold mb-2">Watch Quality</h3>
                        <p class="text-gray-400 text-sm">Auto (up to 1080p)</p>
                    </div>
                    <div class="p-4 bg-gray-800 rounded-lg">
                        <h3 class="font-semibold mb-2">Parental Controls</h3>
                        <p class="text-gray-400 text-sm">Disabled</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>