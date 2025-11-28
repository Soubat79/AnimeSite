<?php
require_once 'common/config.php';

// Get security settings
$security_result = $conn->query("SELECT * FROM site_settings LIMIT 1");
$security = $security_result->fetch_assoc();

// If homepage is locked and user is not logged in, show limited preview
$show_limited_preview = $security['homepage_locked'] && !isset($_SESSION['user_logged_in']);

// Get banners
$banners_result = $conn->query("
    SELECT b.*, m.title, m.poster_url 
    FROM banners b 
    LEFT JOIN movies m ON b.target_movie_id = m.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

// Get categories with their movies
$categories_result = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM movies m WHERE m.category_id = c.id) as movie_count
    FROM categories c 
    ORDER BY c.category_name
");
?>

<?php include 'common/header.php'; ?>

<!-- Limited Access Message -->
<?php if ($show_limited_preview): ?>
<div class="bg-yellow-500 bg-opacity-20 border border-yellow-500 text-yellow-400 p-4 m-4 rounded-lg">
    <div class="flex items-center space-x-3">
        <i class="fas fa-lock text-xl"></i>
        <div>
            <h3 class="font-semibold">Limited Preview</h3>
            <p class="text-sm">Sign in to access all content and features</p>
        </div>
        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
           class="ml-auto bg-crunch-orange hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm transition">
            Sign In
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Banner Slider -->
<section class="relative h-80 overflow-hidden">
    <div class="flex h-full transition-transform duration-500 ease-in-out" id="bannerSlider">
        <?php while($banner = $banners_result->fetch_assoc()): ?>
            <div class="w-full flex-shrink-0 h-full bg-cover bg-center" 
                 style="background-image: url('<?php echo $banner['banner_image_url'] ?: $banner['poster_url']; ?>')">
                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-end">
                    <div class="p-6 w-full">
                        <h2 class="text-2xl font-bold mb-2"><?php echo $banner['title'] ?? 'Featured Content'; ?></h2>
                        <?php if (!$show_limited_preview): ?>
                            <a href="movie_details.php?id=<?php echo $banner['target_movie_id']; ?>" 
                               class="inline-block bg-crunch-orange hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                                Watch Now
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=<?php echo urlencode('movie_details.php?id=' . $banner['target_movie_id']); ?>" 
                               class="inline-block bg-crunch-orange hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                                Sign In to Watch
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Banner Navigation -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <?php for($i = 0; $i < $banners_result->num_rows; $i++): ?>
            <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 transition banner-dot" 
                    data-slide="<?php echo $i; ?>"></button>
        <?php endfor; ?>
    </div>
</section>

<!-- Categories Section -->
<section class="p-4">
    <?php while($category = $categories_result->fetch_assoc()): ?>
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold"><?php echo $category['category_name']; ?></h2>
                <?php if ($show_limited_preview): ?>
                    <span class="text-gray-400 text-sm">Limited Preview</span>
                <?php else: ?>
                    <span class="text-gray-400 text-sm"><?php echo $category['movie_count']; ?> movies</span>
                <?php endif; ?>
            </div>
            
            <div class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide">
                <?php
                $limit = $show_limited_preview ? 3 : 10;
                $movies_result = $conn->query("
                    SELECT * FROM movies 
                    WHERE category_id = {$category['id']} 
                    ORDER BY created_at DESC 
                    LIMIT $limit
                ");
                
                while($movie = $movies_result->fetch_assoc()):
                ?>
                    <a href="<?php echo $show_limited_preview ? 'login.php?redirect=' . urlencode('movie_details.php?id=' . $movie['id']) : 'movie_details.php?id=' . $movie['id']; ?>" 
                       class="flex-shrink-0 w-32 transform hover:scale-105 transition duration-300">
                        <div class="bg-dark-card rounded-lg overflow-hidden shadow-lg relative">
                            <?php if ($show_limited_preview): ?>
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center z-10">
                                    <i class="fas fa-lock text-crunch-orange text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <img src="<?php echo $movie['poster_url']; ?>" 
                                 alt="<?php echo $movie['title']; ?>" 
                                 class="w-full h-48 object-cover"
                                 onerror="this.src='https://via.placeholder.com/300x450/1A1A1A/FFFFFF?text=No+Image'">
                            <div class="p-2">
                                <h3 class="font-semibold text-sm truncate"><?php echo $movie['title']; ?></h3>
                                <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                                    <span><?php echo $movie['release_year']; ?></span>
                                    <span class="text-yellow-400">
                                        <i class="fas fa-star mr-1"></i><?php echo $movie['rating']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
                
                <?php if ($show_limited_preview && $category['movie_count'] > 3): ?>
                    <div class="flex-shrink-0 w-32 flex items-center justify-center">
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="bg-dark-card rounded-lg w-full h-48 flex items-center justify-center flex-col p-4 text-center hover:bg-dark-hover transition">
                            <i class="fas fa-lock text-crunch-orange text-2xl mb-2"></i>
                            <span class="text-sm font-semibold">Sign In to View More</span>
                            <span class="text-xs text-gray-400 mt-1">+<?php echo $category['movie_count'] - 3; ?> more</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</section>

<script>
    // Banner Slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('#bannerSlider > div');
    const dots = document.querySelectorAll('.banner-dot');
    
    function showSlide(n) {
        currentSlide = (n + slides.length) % slides.length;
        document.getElementById('bannerSlider').style.transform = `translateX(-${currentSlide * 100}%)`;
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-opacity-100', index === currentSlide);
            dot.classList.toggle('bg-opacity-50', index !== currentSlide);
        });
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });
    
    // Auto slide every 5 seconds
    setInterval(() => showSlide(currentSlide + 1), 5000);
</script>

<?php include 'common/bottom.php'; ?>