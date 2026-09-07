let patternLevel = 1, patternScore = 0, correctAnswer = "", gameStarted = false, totalCorrect = 0, totalAttempts = 0, gameReady = false;

const patterns = [
    { sequence: ["🔵","🔴","🔵","🔴","?"], answer: "🔵", options: ["🔵","🔴","🟢"] },
    { sequence: ["⭐","❤️","⭐","❤️","?"], answer: "⭐", options: ["❤️","⭐","🌸"] },
    { sequence: ["🔺","🔺","🔵","🔺","🔺","🔵","?"], answer: "🔺", options: ["🔵","🔺","🟢"] },
    { sequence: ["🌸","🌼","🌻","🌸","🌼","?"], answer: "🌻", options: ["🌸","🌻","🌼"] }
];

function startPatternGame() {
    if (!gameReady) { unlockAudio(); gameReady = true; }
    stopBackgroundMusic();
    gameStarted = true;
    const patternIndex = (patternLevel - 1) % patterns.length;
    const currentPattern = patterns[patternIndex];
    correctAnswer = currentPattern.answer;
    document.getElementById("patternDisplay").textContent = currentPattern.sequence.join("   ");
    document.getElementById("patternInstruction").textContent = "Look carefully and choose what comes next.";
    const optionsContainer = document.getElementById("patternOptions");
    optionsContainer.innerHTML = "";
    currentPattern.options.forEach(option => {
        const button = document.createElement("button");
        button.classList.add("pattern-option");
        button.textContent = option;
        button.onclick = function() {
            if (!gameReady) { unlockAudio(); gameReady = true; }
            checkPatternAnswer(option);
        };
        optionsContainer.appendChild(button);
    });
    setTimeout(() => {
        playStartVoice();
        setTimeout(() => startBackgroundMusic(), 1500);
    }, 200);
}

function checkPatternAnswer(selectedAnswer) {
    if (!gameStarted) return;
    gameStarted = false; totalAttempts++;
    const instruction = document.getElementById("patternInstruction");
    if (selectedAnswer === correctAnswer) {
        playLevelUpSound();
        patternScore += 10; totalCorrect++; patternLevel++;
        document.getElementById("patternScore").textContent = patternScore;
        document.getElementById("patternLevel").textContent = patternLevel;
        instruction.textContent = "Excellent! Correct answer.";
    } else {
        playWrongSound();
        instruction.textContent = `Not quite! The correct answer was ${correctAnswer}.`;
        if (patternLevel > 1) playLoseVoice();
    }
    if (patternLevel > 1 && patternLevel % 3 === 0) {
        stopBackgroundMusic();
        playWinVoice();
        saveGameScore('pattern_recognition', patternScore, patternLevel, totalCorrect, totalAttempts);
    }
    setTimeout(() => startPatternGame(), 1500);
}

function saveGameScore(gameType, sc, lv, correct, attempts) {
    const accuracy = Math.round((correct / Math.max(attempts, 1)) * 100);
    const diff = lv <= 3 ? 'easy' : lv <= 6 ? 'medium' : 'hard';
    fetch('/SmritiMitra/api/games.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'save_score', game_type: gameType, score: sc, level: lv, difficulty: diff, accuracy: accuracy })
    }).catch(() => {});
}
