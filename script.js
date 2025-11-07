// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Search functionality
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const searchResults = document.getElementById('searchResults');

// Sample anime data for search
const animeData = [
    { title: "Naruto: Shippuden", genre: "Action, Adventure", link: "anime/naruto.html" },
    { title: "One Piece", genre: "Action, Adventure, Comedy", link: "anime/onepiece.html" },
    { title: "The Angel Next Door Spoils Me Rotten", genre: "Romance, Comedy, Slice of Life", link: "anime/angel-next-door.html" }
    { title: "Demon Slayer: Kimetsu no Yaiba", genre: "Action, Supernatural", link: "anime/demon-slayer.html" },
    { title: "Attack on Titan", genre: "Action, Drama, Fantasy", link: "anime/attack-on-titan.html" },
    { title: "Jujutsu Kaisen", genre: "Action, Supernatural", link: "#" },
    { title: "Tokyo Revengers", genre: "Action, Drama, Supernatural", link: "#" },
    { title: "Spy x Family", genre: "Action, Comedy, Slice of Life", link: "#" },
    { title: "Chainsaw Man", genre: "Action, Horror", link: "#" },
    { title: "My Hero Academia", genre: "Action, Superhero", link: "#" },
    { title: "Dragon Ball Z", genre: "Action, Adventure", link: "#" }
];

searchBtn.addEventListener('click', function() {
    performSearch();
});

searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});

searchInput.addEventListener('input', function() {
    if (this.value.length > 0) {
        showSearchResults(this.value);
    } else {
        hideSearchResults();
    }
});

function performSearch() {
    const searchTerm = searchInput.value.trim();
    if (searchTerm) {
        // In a real implementation, you would redirect to search results page
        // For now, we'll just show an alert
        alert(`Searching for: ${searchTerm}`);
        hideSearchResults();
    }
}

function showSearchResults(query) {
    const results = animeData.filter(anime => 
        anime.title.toLowerCase().includes(query.toLowerCase())
    );
    
    if (results.length > 0) {
        searchResults.innerHTML = '';
        results.forEach(anime => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.innerHTML = `
                <h4>${anime.title}</h4>
                <p>${anime.genre}</p>
            `;
            resultItem.addEventListener('click', function() {
                window.location.href = anime.link;
            });
            searchResults.appendChild(resultItem);
        });
        searchResults.classList.add('active');
    } else {
        searchResults.innerHTML = '<div class="search-result-item">No results found</div>';
        searchResults.classList.add('active');
    }
}

function hideSearchResults() {
    searchResults.classList.remove('active');
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        hideSearchResults();
    }
});

// Anime card click handler
const animeCards = document.querySelectorAll('.anime-card');
animeCards.forEach(card => {
    card.addEventListener('click', function() {
        const anime = this.getAttribute('data-anime');
        if (anime) {
            window.location.href = `anime/${anime}.html`;
        }
    });
});

// Video player functionality (for anime detail pages)
function playEpisode(episodeNumber, title, description, videoUrl = null) {
    // Use the provided video URL or the default one
    const videoSrc = videoUrl || "https://short.icu/MIcxKWDU5";
    
    const videoContainer = document.getElementById('videoContainer');
    const videoTitle = document.getElementById('videoTitle');
    const videoDescription = document.getElementById('videoDescription');
    
    if (videoContainer && videoTitle && videoDescription) {
        videoContainer.innerHTML = `
            <iframe width="100%" height="100%" src="${videoSrc}" frameborder="0" scrolling="0" allowfullscreen></iframe>
        `;
        videoTitle.textContent = title;
        videoDescription.textContent = description;
        
        // Scroll to video player
        videoContainer.scrollIntoView({ behavior: 'smooth' });
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // If we're on an anime detail page, set up episode list
    const episodeItems = document.querySelectorAll('.episode-item');
    episodeItems.forEach(item => {
        item.addEventListener('click', function() {
            const episodeNumber = this.getAttribute('data-episode');
            const title = this.querySelector('h4').textContent;
            const description = this.querySelector('p').textContent;
            const videoUrl = this.getAttribute('data-video-url');
            playEpisode(episodeNumber, title, description, videoUrl);
        });
    });
    
    // Auto-play first episode if on anime detail page
    if (window.location.pathname.includes('/anime/') && episodeItems.length > 0) {
        const firstEpisode = episodeItems[0];
        const episodeNumber = firstEpisode.getAttribute('data-episode');
        const title = firstEpisode.querySelector('h4').textContent;
        const description = firstEpisode.querySelector('p').textContent;
        const videoUrl = firstEpisode.getAttribute('data-video-url');
        playEpisode(episodeNumber, title, description, videoUrl);
    }
    
    // Next/Previous episode controls
    const prevBtn = document.getElementById('prevEpisode');
    const nextBtn = document.getElementById('nextEpisode');
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
            // Implementation for previous episode
            alert('Previous episode functionality would go here');
        });
        
        nextBtn.addEventListener('click', function() {
            // Implementation for next episode
            alert('Next episode functionality would go here');
        });
    }
    
    // PWA Installation
    initializePWA();
});

// PWA Installation functionality
let deferredPrompt;
const installButton = document.getElementById('installButton');

function initializePWA() {
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                })
                .catch(function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
        });
    }

    // Add to Home Screen prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install button
        if (installButton) {
            installButton.style.display = 'flex';
        }
        
        installButton.addEventListener('click', installApp);
    });
}

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                installButton.style.display = 'none';
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
        });
    }
}

// Check if app is already installed
window.addEventListener('appinstalled', (evt) => {
    console.log('AnimeFlix app was installed.');
    if (installButton) {
        installButton.style.display = 'none';
    }
});
