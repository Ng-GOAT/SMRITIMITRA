<?php
session_start();
include "../config/db.php";
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="game-page-header">
            <div>
                <p class="section-tag">COGNITIVE TRAINING</p>
                <h1>Memory Match</h1>
                <p>Find matching pairs and strengthen your memory.</p>
            </div>
            <a href="games.php" class="back-link">Back to Games</a>
        </section>

        <div id="startScreen" style="text-align:center;padding:60px 20px;background:white;border-radius:20px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:25px;">
            <div style="font-size:60px;margin-bottom:15px;">&#x1F9E0;</div>
            <h2 style="margin-bottom:10px;">Memory Match</h2>
            <p style="color:#64748b;margin-bottom:25px;">Find matching pairs to win!</p>
            <button id="startBtn" style="padding:18px 50px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:14px;font-size:18px;font-weight:700;cursor:pointer;">Start Game</button>
        </div>

        <div class="game-stats" id="gameStats" style="display:none;">
            <div class="game-stat-card"><span>Moves</span><strong id="moves">0</strong></div>
            <div class="game-stat-card"><span>Matches</span><strong id="matches">0 / 4</strong></div>
        </div>

        <div class="memory-game-container" id="gameArea" style="display:none;">
            <div class="memory-board" id="memoryBoard"></div>
        </div>

        <div class="game-actions" id="gameActions" style="display:none;">
            <button class="restart-game-btn" id="restartBtn">Restart Game</button>
        </div>

        <div id="gameResult" class="game-result hidden">
            <h2>Excellent!</h2>
            <p id="resultText"></p>
            <button class="restart-game-btn" id="playAgainBtn">Play Again</button>
        </div>
    </main>
</div>

<script>
var audioCtx = null;
var bgMusicInterval = null;
var isMuted = localStorage.getItem("gameSoundMuted") === "true";

function unlockAudio() {
    if (audioCtx) return;
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === "suspended") audioCtx.resume();
    } catch(e) {}
}

function playTone(freq, dur, type, vol) {
    if (isMuted || !audioCtx) return;
    try {
        var o = audioCtx.createOscillator();
        var g = audioCtx.createGain();
        o.type = type || "sine";
        o.frequency.setValueAtTime(freq, audioCtx.currentTime);
        g.gain.setValueAtTime(vol || 0.15, audioCtx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + dur);
        o.connect(g); g.connect(audioCtx.destination);
        o.start(audioCtx.currentTime); o.stop(audioCtx.currentTime + dur);
    } catch(e) {}
}

function playMatchSound() { playTone(523,0.12,"sine",0.2); setTimeout(function(){playTone(659,0.12,"sine",0.2)},120); setTimeout(function(){playTone(784,0.18,"sine",0.2)},240); }
function playWrongSound() { playTone(200,0.25,"sawtooth",0.12); }
function playFlipSound() { playTone(440,0.07,"sine",0.12); }
function playWinSound() { [523,659,784,1047,784,1047,1319].forEach(function(n,i){setTimeout(function(){playTone(n,0.18,"sine",0.18)},i*120)}); }

function speakText(text) {
    if (isMuted || !("speechSynthesis" in window)) return;
    window.speechSynthesis.cancel();
    var u = new SpeechSynthesisUtterance(text);
    u.lang = "en-IN"; u.rate = 0.9; u.pitch = 1.1;
    window.speechSynthesis.speak(u);
}

function stopBG() { if (bgMusicInterval) { clearInterval(bgMusicInterval); bgMusicInterval = null; } }
function startBG() {
    if (isMuted) return;
    var notes = [262,294,330,349,392,349,330,294]; var idx = 0;
    function play() {
        if (isMuted) return;
        try { var o = audioCtx.createOscillator(); var g = audioCtx.createGain();
        o.type="sine"; o.frequency.setValueAtTime(notes[idx%notes.length],audioCtx.currentTime);
        g.gain.setValueAtTime(0.05,audioCtx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001,audioCtx.currentTime+0.45);
        o.connect(g); g.connect(audioCtx.destination);
        o.start(audioCtx.currentTime); o.stop(audioCtx.currentTime+0.45); idx++; } catch(e){}
    }
    play(); bgMusicInterval = setInterval(play, 500);
}

var cardMap = {"A":"\uD83C\uDF4E","B":"\uD83D\uDC36","C":"\uD83C\uDF38","D":"\uD83D\uDE97"};
var cards=[], firstCard=null, secondCard=null, moves=0, matches=0, locked=false, totalAttempts=0;

function startGame() {
    unlockAudio(); stopBG();
    cards = ["A","A","B","B","C","C","D","D"].sort(function(){return Math.random()-0.5});
    firstCard=null; secondCard=null; moves=0; matches=0; locked=false; totalAttempts=0;
    document.getElementById("moves").textContent="0";
    document.getElementById("matches").textContent="0 / 4";
    document.getElementById("gameResult").classList.add("hidden");
    document.getElementById("startScreen").style.display="none";
    document.getElementById("gameStats").style.display="grid";
    document.getElementById("gameArea").style.display="block";
    document.getElementById("gameActions").style.display="flex";
    var board = document.getElementById("memoryBoard"); board.innerHTML="";
    cards.forEach(function(v,i){
        var c = document.createElement("div"); c.classList.add("memory-card");
        c.dataset.value=v; c.textContent="?";
        c.addEventListener("click", function(){flipCard(c)});
        board.appendChild(c);
    });
    speakText("Lets start the game! Good luck!");
    setTimeout(startBG, 1500);
}

function flipCard(card) {
    if (locked || card.classList.contains("flipped") || card.classList.contains("matched")) return;
    unlockAudio(); playFlipSound();
    card.classList.add("flipped"); card.textContent=cardMap[card.dataset.value]||card.dataset.value;
    if (!firstCard) { firstCard=card; }
    else { secondCard=card; moves++; totalAttempts++; document.getElementById("moves").textContent=moves; checkMatch(); }
}

function checkMatch() {
    locked=true;
    if (firstCard.dataset.value===secondCard.dataset.value) {
        playMatchSound();
        firstCard.classList.add("matched"); secondCard.classList.add("matched"); matches++;
        document.getElementById("matches").textContent=matches+" / 4";
        firstCard=null; secondCard=null; locked=false;
        if (matches===4) { stopBG(); setTimeout(function(){ playWinSound(); speakText("Hurray! You won! Thats amazing!");
            document.getElementById("gameResult").classList.remove("hidden");
            document.getElementById("resultText").textContent="You won in "+moves+" moves!";
            saveScore(moves,totalAttempts); },500); }
    } else {
        playWrongSound();
        setTimeout(function(){ firstCard.classList.remove("flipped"); secondCard.classList.remove("flipped");
        firstCard.textContent="?"; secondCard.textContent="?"; firstCard=null; secondCard=null; locked=false; },800);
    }
}

function saveScore(score, attempts) {
    var acc = Math.round((matches*2/Math.max(attempts,1))*100);
    fetch("/SmritiMitra/api/games.php",{method:"POST",body:new URLSearchParams({action:"save_score",game_type:"memory_match",score:Math.max(0,100-score*2),level:1,difficulty:"easy",accuracy:acc})}).catch(function(){});
}
</script>

<script>
document.getElementById("startBtn").addEventListener("click", function(){ startGame(); });
document.getElementById("restartBtn").addEventListener("click", function(){ startGame(); });
document.getElementById("playAgainBtn").addEventListener("click", function(){ startGame(); });
</script>
</body>
</html>
