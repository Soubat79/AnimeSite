<?php
require_once 'common/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$movie_id = intval($_GET['id']);
$movie_result = $conn->query("
    SELECT m.*, c.category_name 
    FROM movies m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.id = $movie_id
");

if ($movie_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$movie = $movie_result->fetch_assoc();

// Parse episode list if exists
$episodes = [];
if (!empty($movie['episode_list'])) {
    $episodes = json_decode($movie['episode_list'], true);
}

// Check if user needs to login for premium content
$requires_login = false;
if (count($episodes) > 3 && !isset($_SESSION['user_logged_in'])) {
    $requires_login = true;
}
?>

<?php include 'common/header.php'; ?>

<!-- Crunchyroll-style Video Player Layout -->
<div class="min-h-screen bg-dark-bg">
    <!-- Video Player Section -->
    <section class="relative bg-black">
        <?php if ($requires_login): ?>
            <!-- Login Required Overlay -->
            <div class="aspect-video bg-gray-900 flex items-center justify-center">
                <div class="text-center p-8">
                    <div class="w-20 h-20 bg-crunch-orange rounded-full mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-lock text-2xl text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Premium Content</h2>
                    <p class="text-gray-400 mb-6">Sign in to watch this series</p>
                    <div class="flex space-x-4 justify-center">
                        <a href="login.php?mode=login" class="bg-crunch-orange hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                            Sign In
                        </a>
                        <a href="login.php?mode=signup" class="border border-gray-600 hover:border-crunch-orange text-white px-6 py-3 rounded-lg font-semibold transition">
                            Sign Up
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Video Player -->
            <div id="videoContainer" class="aspect-video bg-black relative">
                <!-- Default placeholder -->
                <div id="videoPlaceholder" class="w-full h-full flex items-center justify-center bg-gray-900">
                    <div class="text-center">
                        <i class="fas fa-play-circle text-6xl text-crunch-orange mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Click an episode to play</h3>
                        <p class="text-gray-400">Select an episode from the list below</p>
                    </div>
                </div>
                
                <!-- Video Player (hidden by default) -->
                <div id="videoPlayer" class="hidden w-full h-full">
                    <iframe id="videoFrame" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                </div>
                
                <!-- Player Controls Overlay -->
                <div id="playerControls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6 hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button onclick="togglePlay()" class="w-12 h-12 bg-crunch-orange rounded-full flex items-center justify-center hover:bg-orange-600 transition">
                                <i class="fas fa-pause text-white"></i>
                            </button>
                            <div class="text-white">
                                <h4 id="nowPlaying" class="font-semibold">Now Playing</h4>
                                <p id="episodeTitle" class="text-sm text-gray-300"></p>
                            </div>
                        </div>
                        <button onclick="toggleFullscreen()" class="text-white hover:text-crunch-orange transition">
                            <i class="fas fa-expand text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Content Below Player -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Series Info -->
                <div class="bg-dark-card rounded-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        <img src="<?php echo $movie['poster_url']; ?>" 
                             alt="<?php echo $movie['title']; ?>" 
                             class="w-32 h-48 object-cover rounded-lg flex-shrink-0"
                             onerror="this.src='https://via.placeholder.com/300x450/1A1A1A/FFFFFF?text=No+Image'">
                        
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <h1 class="text-2xl font-bold mr-4"><?php echo $movie['title']; ?></h1>
                                <span class="bg-crunch-orange px-2 py-1 rounded text-sm">HD</span>
                                <span class="bg-blue-500 px-2 py-1 rounded text-sm"><?php echo $movie['category_name']; ?></span>
                                <span class="text-yellow-400 text-sm">
                                    <i class="fas fa-star mr-1"></i><?php echo $movie['rating']; ?>
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap gap-4 text-sm text-gray-400 mb-4">
                                <span><?php echo $movie['release_year']; ?></span>
                                <span><?php echo count($episodes); ?> Episodes</span>
                                <span>TV Series</span>
                            </div>
                            
                            <p class="text-gray-300 leading-relaxed"><?php echo $movie['description']; ?></p>
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 mt-4">
                                <?php if (!empty($episodes) && !$requires_login): ?>
                                    <button onclick="playFirstEpisode()" class="bg-crunch-orange hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
                                        <i class="fas fa-play mr-2"></i>Play First Episode
                                    </button>
                                <?php endif; ?>
                                <button class="border border-gray-600 hover:border-crunch-orange text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Add to Watchlist
                                </button>
                                <button class="border border-gray-600 hover:border-crunch-orange text-white px-6 py-3 rounded-lg font-semibold transition flex items-center">
                                    <i class="fas fa-share mr-2"></i>Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Episodes List -->
                <?php if (!empty($episodes)): ?>
                    <div class="bg-dark-card rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold">Episodes</h2>
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-400"><?php echo count($episodes); ?> episodes</span>
                                <select class="bg-gray-800 border border-gray-600 rounded px-3 py-2 text-white">
                                    <option>Season 1</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <?php foreach ($episodes as $index => $episode): ?>
                                <div class="episode-item bg-gray-800 rounded-lg p-4 hover:bg-dark-hover transition cursor-pointer border-l-4 border-transparent hover:border-crunch-orange"
                                     onclick="playEpisode(<?php echo $index; ?>)"
                                     data-episode-index="<?php echo $index; ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center relative">
                                                <i class="fas fa-play text-crunch-orange"></i>
                                                <?php if ($index === 0): ?>
                                                    <div class="absolute -top-1 -right-1 bg-crunch-orange text-white text-xs px-2 py-1 rounded">
                                                        NEW
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold">Episode <?php echo $index + 1; ?></h3>
                                                <p class="text-gray-400 text-sm"><?php echo $episode['title']; ?></p>
                                                <div class="flex items-center space-x-3 text-xs text-gray-500 mt-1">
                                                    <span><?php echo $episode['duration']; ?></span>
                                                    <span>•</span>
                                                    <span>HD</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-gray-400 text-sm"><?php echo $episode['duration']; ?></span>
                                            <div class="text-crunch-orange text-sm mt-1">
                                                <i class="fas fa-play-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- More Like This -->
                <div class="bg-dark-card rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">More Like This</h3>
                    <div class="space-y-4">
                        <?php
                        $similar_movies = $conn->query("
                            SELECT * FROM movies 
                            WHERE category_id = {$movie['category_id']} AND id != {$movie['id']} 
                            ORDER BY RAND() 
                            LIMIT 3
                        ");
                        
                        while($similar = $similar_movies->fetch_assoc()):
                        ?>
                            <a href="movie_details.php?id=<?php echo $similar['id']; ?>" class="flex items-center space-x-3 hover:bg-dark-hover p-2 rounded transition">
                                <img src="<?php echo $similar['poster_url']; ?>" 
                                     alt="<?php echo $similar['title']; ?>" 
                                     class="w-16 h-24 object-cover rounded"
                                     onerror="this.src='https://via.placeholder.com/300x400/1A1A1A/FFFFFF?text=No+Image'">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm line-clamp-2"><?php echo $similar['title']; ?></h4>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <span class="text-yellow-400">
                                            <i class="fas fa-star mr-1"></i><?php echo $similar['rating']; ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Series Info -->
                <div class="bg-dark-card rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Series Info</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="text-green-400">Ongoing</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Episodes:</span>
                            <span><?php echo count($episodes); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Rating:</span>
                            <span class="text-yellow-400"><?php echo $movie['rating']; ?>/10</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Genre:</span>
                            <span><?php echo $movie['category_name']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const episodes = <?php echo json_encode($episodes); ?>;
    let currentEpisodeIndex = 0;
    let isPlaying = false;

    function playEpisode(index) {
        if (index >= episodes.length) return;
        
        currentEpisodeIndex = index;
        const episode = episodes[index];
        
        // Update UI
        document.getElementById('nowPlaying').textContent = `Episode ${index + 1}`;
        document.getElementById('episodeTitle').textContent = episode.title;
        
        // Show video player
        document.getElementById('videoPlaceholder').classList.add('hidden');
        document.getElementById('videoPlayer').classList.remove('hidden');
        document.getElementById('playerControls').classList.remove('hidden');
        
        // Set video source
        const videoFrame = document.getElementById('videoFrame');
        let videoUrl = episode.video_url;
        
        // Convert YouTube URLs to embed format
        if (videoUrl.includes('youtube.com/watch?v=')) {
            const videoId = videoUrl.split('v=')[1].split('&')[0];
            videoUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
        } else if (videoUrl.includes('youtu.be/')) {
            const videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
            videoUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
        } else if (videoUrl.includes('short.icu')) {
            // For short.icu links, we'll use the direct URL
            videoUrl = videoUrl;
        }
        
        videoFrame.src = videoUrl;
        isPlaying = true;
        
        // Update episode list styling
        document.querySelectorAll('.episode-item').forEach(item => {
            item.classList.remove('bg-crunch-orange', 'bg-opacity-20');
            item.classList.add('bg-gray-800');
        });
        
        const currentItem = document.querySelector(`[data-episode-index="${index}"]`);
        if (currentItem) {
            currentItem.classList.remove('bg-gray-800');
            currentItem.classList.add('bg-crunch-orange', 'bg-opacity-20');
        }
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function playFirstEpisode() {
        if (episodes.length > 0) {
            playEpisode(0);
        }
    }

    function togglePlay() {
        // This would control video playback in a real implementation
        const button = document.querySelector('#playerControls button');
        const icon = button.querySelector('i');
        
        if (isPlaying) {
            icon.classList.remove('fa-pause');
            icon.classList.add('fa-play');
        } else {
            icon.classList.remove('fa-play');
            icon.classList.add('fa-pause');
        }
        
        isPlaying = !isPlaying;
    }

    function toggleFullscreen() {
        const container = document.getElementById('videoContainer');
        
        if (!document.fullscreenElement) {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Auto-play first episode if it's the only one
    <?php if (count($episodes) === 1 && !$requires_login): ?>
    document.addEventListener('DOMContentLoaded', function() {
        playFirstEpisode();
    });
    <?php endif; ?>

    // Handle episode navigation with keyboard
    document.addEventListener('keydown', function(e) {
        if (!isPlaying) return;
        
        switch(e.key) {
            case 'ArrowRight':
                e.preventDefault();
                if (currentEpisodeIndex < episodes.length - 1) {
                    playEpisode(currentEpisodeIndex + 1);
                }
                break;
            case 'ArrowLeft':
                e.preventDefault();
                if (currentEpisodeIndex > 0) {
                    playEpisode(currentEpisodeIndex - 1);
                }
                break;
            case ' ':
                e.preventDefault();
                togglePlay();
                break;
        }
    });
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .episode-item {
        transition: all 0.3s ease;
    }
    
    .episode-item:hover {
        transform: translateX(4px);
    }
</style>

<?php include 'common/bottom.php'; ?>