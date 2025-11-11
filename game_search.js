async function searchGames(query) {
    const response = await fetch(`igdb_api.php?query=${encodeURIComponent(query)}`);
    const games = await response.json();
    displayGames(games);
}
function displayGames(games) {
    const container = document.getElementById('games-container');
    container.innerHTML = games.map(game => `
        <div class="game-card">
            <img src="${game.cover?.url || 'default.jpg'}" alt="${game.name}">
            <h3>${game.name}</h3>
            <p>${game.summary?.substring(0,100)}...</p>
            <a href="game_detail.html?id=${game.id}">View Details</a>
        </div>
    `).join('');
}
