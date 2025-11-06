const animeList = [
  { title: "Naruto", image: "images/naruto.jpg", page: "anime/naruto.html", tags:["ninja","action"], info:"Action | many eps" },
  { title: "One Piece", image: "images/onepiece.jpg", page: "anime/onepiece.html", tags:["pirate","adventure"], info:"Adventure | ongoing" },
  { title: "Demon Slayer", image: "images/demon-slayer.jpg", page: "anime/demon-slayer.html", tags:["dark","fantasy"], info:"Fantasy | 26 eps" },
  { title: "Attack on Titan", image: "images/aot.jpg", page: "anime/attack-on-titan.html", tags:["dark","action"], info:"Drama | 75 eps" }
];

const grid = document.getElementById('animeGrid');
function render(list){
  grid.innerHTML = '';
  list.forEach(item => {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `<a href="${item.page}"><img src="${item.image}" class="thumb" alt="${item.title}"><div class="meta"><h3>${item.title}</h3><p>${item.info||''}</p></div></a>`;
    grid.appendChild(card);
  });
}
render(animeList);

const input = document.getElementById('searchInput');
input.addEventListener('input', e => {
  const q = e.target.value.toLowerCase().trim();
  if(!q) return render(animeList);
  const filtered = animeList.filter(a => a.title.toLowerCase().includes(q) || (a.tags && a.tags.join(' ').toLowerCase().includes(q)));
  render(filtered);
});
