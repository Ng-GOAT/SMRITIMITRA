let sequence = [], userAnswer = [], level = 1, score = 0, gameActive = false, totalCorrect = 0, totalAttempts = 0, gameReady = false;

function startSequence() {
    if (!gameReady) { unlockAudio(); gameReady = true; }
    stopBackgroundMusic();
    sequence = []; userAnswer = []; gameActive = false; updateAnswer();
    document.getElementById("gameInstruction").textContent = "Remember the numbers carefully...";
    const sequenceLength = level + 2;
    for (let i = 0; i < sequenceLength; i++) sequence.push(Math.floor(Math.random() * 9) + 1);
    const display = document.getElementById("sequenceDisplay");
    display.textContent = sequence.join("  ");
    document.getElementById("startButton").disabled = true;
    setTimeout(() => {
        playStartVoice();
        setTimeout(() => startBackgroundMusic(), 1500);
    }, 200);
    setTimeout(() => {
        display.textContent = "?"; gameActive = true;
        document.getElementById("gameInstruction").textContent = "Now repeat the sequence using the number buttons.";
        document.getElementById("startButton").disabled = false;
    }, 3000);
}

function addNumber(number) {
    if (!gameActive) return;
    if (!gameReady) { unlockAudio(); gameReady = true; }
    playClickSound();
    userAnswer.push(number); updateAnswer();
    if (userAnswer.length === sequence.length) checkAnswer();
}

function updateAnswer() {
    const a = document.getElementById("userSequence");
    a.textContent = userAnswer.length > 0 ? userAnswer.join("  ") : "-";
}

function clearAnswer() { if (!gameActive) return; userAnswer = []; updateAnswer(); }

function checkAnswer() {
    gameActive = false; totalAttempts++;
    const correct = JSON.stringify(sequence) === JSON.stringify(userAnswer);
    const display = document.getElementById("sequenceDisplay");
    if (correct) {
        playLevelUpSound();
        score += 10; totalCorrect++; document.getElementById("score").textContent = score;
        document.getElementById("gameInstruction").textContent = "Excellent! Correct sequence.";
        display.textContent = "Correct!"; level++;
        document.getElementById("level").textContent = level;
        setTimeout(() => startSequence(), 1500);
    } else {
        playWrongSound();
        document.getElementById("gameInstruction").textContent = "Try again! The correct sequence was:";
        display.textContent = sequence.join("  ");
        if (level > 1) playLoseVoice();
        setTimeout(() => startSequence(), 2500);
    }
    if (level > 1 && level % 3 === 0) {
        stopBackgroundMusic();
        playWinVoice();
        saveGameScore('number_sequence', score, level, totalCorrect, totalAttempts);
    }
}

function saveGameScore(gameType, sc, lv, correct, attempts) {
    const accuracy = Math.round((correct / Math.max(attempts, 1)) * 100);
    const diff = lv <= 3 ? 'easy' : lv <= 6 ? 'medium' : 'hard';
    fetch('/SmritiMitra/api/games.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'save_score', game_type: gameType, score: sc, level: lv, difficulty: diff, accuracy: accuracy })
    }).catch(() => {});
}
