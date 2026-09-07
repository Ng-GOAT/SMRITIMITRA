const cardValues = ["A","A","B","B","C","C","D","D"];
let cards = [], firstCard = null, secondCard = null, moves = 0, matches = 0, locked = false;
let gameStartTime = 0, totalAttempts = 0;

function startGame() {
    unlockAudio();
    stopBackgroundMusic();
    cards = [...cardValues].sort(function() { return Math.random() - 0.5; });
    firstCard = null; secondCard = null; moves = 0; matches = 0; locked = false;
    totalAttempts = 0; gameStartTime = Date.now();
    document.getElementById("moves").textContent = moves;
    document.getElementById("matches").textContent = "0 / 4";
    document.getElementById("gameResult").classList.add("hidden");
    document.getElementById("startScreen").style.display = "none";
    document.getElementById("gameStats").style.display = "grid";
    document.getElementById("gameArea").style.display = "block";
    document.getElementById("gameActions").style.display = "flex";
    createBoard();
    playStartVoice();
    setTimeout(function() { startBackgroundMusic(); }, 1500);
}

function createBoard() {
    var board = document.getElementById("memoryBoard");
    board.innerHTML = "";
    var emojis = ["\uD83C\uDF4E","\uD83D\uDC36","\uD83C\uDF38","\uD83D\uDE97"];
    cards.forEach(function(value, index) {
        var card = document.createElement("div");
        card.classList.add("memory-card");
        card.dataset.value = value;
        card.dataset.index = index;
        card.textContent = "?";
        card.addEventListener("click", function() { flipCard(card); });
        board.appendChild(card);
    });
}

function flipCard(card) {
    if (locked || card.classList.contains("flipped") || card.classList.contains("matched")) return;
    playFlipSound();
    card.classList.add("flipped");
    var emojiMap = {"A":"\uD83C\uDF4E","B":"\uD83D\uDC36","C":"\uD83C\uDF38","D":"\uD83D\uDE97"};
    card.textContent = emojiMap[card.dataset.value] || card.dataset.value;
    if (firstCard === null) { firstCard = card; }
    else { secondCard = card; moves++; totalAttempts++; document.getElementById("moves").textContent = moves; checkMatch(); }
}

function checkMatch() {
    locked = true;
    var isMatch = firstCard.dataset.value === secondCard.dataset.value;
    if (isMatch) {
        playMatchSound();
        firstCard.classList.add("matched"); secondCard.classList.add("matched");
        matches++;
        document.getElementById("matches").textContent = matches + " / 4";
        resetTurn();
        if (matches === 4) {
            stopBackgroundMusic();
            setTimeout(function() {
                playWinSound();
                playWinVoice();
                document.getElementById("gameResult").classList.remove("hidden");
                document.getElementById("resultText").textContent = "You completed the game in " + moves + " moves!";
                saveGameScore("memory_match", moves, totalAttempts);
            }, 500);
        }
    } else {
        playWrongSound();
        setTimeout(function() {
            firstCard.classList.remove("flipped"); secondCard.classList.remove("flipped");
            firstCard.textContent = "?"; secondCard.textContent = "?";
            resetTurn();
        }, 800);
    }
}

function resetTurn() { firstCard = null; secondCard = null; locked = false; }

function saveGameScore(gameType, score, attempts) {
    var accuracy = Math.round((matches * 2 / Math.max(attempts, 1)) * 100);
    var level = score <= 8 ? "easy" : score <= 15 ? "medium" : "hard";
    fetch("/SmritiMitra/api/games.php", {
        method: "POST",
        body: new URLSearchParams({ action: "save_score", game_type: gameType, score: Math.max(0, 100 - score * 2), level: 1, difficulty: level, accuracy: accuracy })
    }).catch(function() {});
}
