const searchInput = document.getElementById("search");
const animeList = document.getElementById("animeList");
const cards = animeList.getElementsByClassName("anime-card");

searchInput.addEventListener("keyup", () => {
  const filter = searchInput.value.toLowerCase();
  for (let card of cards) {
    const title = card.textContent.toLowerCase();
    card.style.display = title.includes(filter) ? "block" : "none";
  }
});
