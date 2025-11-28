<?php
require_once 'common/config.php';

$categories_result = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM movies m WHERE m.category_id = c.id) as movie_count
    FROM categories c 
    ORDER BY c.category_name
");

$selected_category = null;
$category_movies = [];

if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $selected_category_result = $conn->query("SELECT * FROM categories WHERE id = $category_id");
    
    if ($selected_category_result->num_rows > 0) {
        $selected_category = $selected_category_result->fetch_assoc();
        
        $category_movies_result = $conn->query("
            SELECT * FROM movies 
            WHERE category_id = $category_id 
            ORDER BY title
        ");
        
        while($movie = $category_movies_result->fetch_assoc()) {
            $category_movies[] = $movie;
        }
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="p-4">
    <?php if (!$selected_category): ?>
        <!-- All Categories -->
        <h1 class="text-2xl font-bold mb-6">Categories</h1>
        
        <div class="grid grid-cols-2 gap-4">
            <?php while($category = $categories_result->fetch_assoc()): ?>
                <a href="categories_page.php?category=<?php echo $category['id']; ?>" 
                   class="bg-dark-card rounded-lg p-6 text-center hover:bg-crunch-orange transition duration-300 transform hover:scale-105">
                    <div class="text-3xl mb-3">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3 class="font-semibold text-lg"><?php echo $category['category_name']; ?></h3>
                    <p class="text-gray-400 text-sm mt-1"><?php echo $category['movie_count']; ?> movies</p>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <!-- Specific Category -->
        <div class="mb-6">
            <a href="categories_page.php" class="inline-flex items-center text-crunch-orange mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Back to Categories
            </a>
            <h1 class="text-2xl font-bold"><?php echo $selected_category['category_name']; ?></h1>
            <p class="text-gray-400"><?php echo count($category_movies); ?> movies</p>
        </div>

        <?php if (empty($category_movies)): ?>
            <div class="text-center py-12">
                <i class="fas fa-film text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">No movies in this category</h3>
                <p class="text-gray-400">Check back later for new additions</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach($category_movies as $movie): ?>
                    <a href="movie_details.php?id=<?php echo $movie['id']; ?>" class="block transform hover:scale-105 transition duration-300">
                        <div class="bg-dark-card rounded-lg overflow-hidden shadow-lg">
                            <img src="<?php echo $movie['poster_url']; ?>" 
                                 alt="<?php echo $movie['title']; ?>" 
                                 class="w-full h-64 object-cover"
                                 onerror="this.src='https://via.placeholder.com/300x400/1A1A1A/FFFFFF?text=No+Image'">
                            <div class="p-3">
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>