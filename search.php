<?php
require_once 'common/config.php';

$search_term = '';
$results = [];

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = $conn->real_escape_string($_GET['q']);
    $results_result = $conn->query("
        SELECT * FROM movies 
        WHERE title LIKE '%$search_term%' 
        OR description LIKE '%$search_term%' 
        ORDER BY title
    ");
    
    while($row = $results_result->fetch_assoc()) {
        $results[] = $row;
    }
}
?>

<?php include 'common/header.php'; ?>

<div class="p-4">
    <!-- Search Form -->
    <form method="GET" class="mb-6">
        <div class="relative">
            <input type="text" 
                   name="q" 
                   value="<?php echo htmlspecialchars($search_term); ?>" 
                   placeholder="Search movies..." 
                   class="w-full bg-dark-card border border-gray-600 rounded-lg py-3 px-4 pl-12 text-white focus:outline-none focus:border-crunch-orange">
            <div class="absolute left-4 top-3 text-gray-400">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </form>

    <!-- Search Results -->
    <?php if (!empty($search_term)): ?>
        <div class="mb-4">
            <h2 class="text-xl font-bold">
                Search Results for "<?php echo htmlspecialchars($search_term); ?>"
                <span class="text-gray-400 text-sm">(<?php echo count($results); ?> results)</span>
            </h2>
        </div>
    <?php endif; ?>

    <?php if (empty($search_term)): ?>
        <!-- Popular Searches -->
        <div class="text-center py-12">
            <i class="fas fa-search text-4xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Search for Movies</h3>
            <p class="text-gray-400">Find your favorite movies and shows</p>
            
            <!-- Popular Categories -->
            <div class="mt-8">
                <h4 class="font-semibold mb-4">Popular Categories</h4>
                <div class="flex flex-wrap justify-center gap-2">
                    <?php
                    $popular_cats = $conn->query("SELECT * FROM categories LIMIT 8");
                    while($cat = $popular_cats->fetch_assoc()):
                    ?>
                        <a href="categories_page.php?category=<?php echo $cat['id']; ?>" 
                           class="bg-dark-card hover:bg-crunch-orange px-4 py-2 rounded-full transition">
                            <?php echo $cat['category_name']; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php elseif (empty($results)): ?>
        <!-- No Results -->
        <div class="text-center py-12">
            <i class="fas fa-search text-4xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">No results found</h3>
            <p class="text-gray-400">Try different keywords or browse categories</p>
        </div>
    <?php else: ?>
        <!-- Results Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach($results as $movie): ?>
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
</div>

<?php include 'common/bottom.php'; ?>