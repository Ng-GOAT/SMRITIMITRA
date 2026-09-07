let audioCtx = null;
let bgMusicInterval = null;
let isMuted = localStorage.getItem('gameSoundMuted') === 'true';
let audioUnlocked = false;

function unlockAudio() {
    if (audioUnlocked) return;
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        audioUnlocked = true;
    } catch(e) {}
}

document.addEventListener('click', unlockAudio, { once: true });
document.addEventListener('touchstart', unlockAudio, { once: true });

function getAudioContext() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    return audioCtx;
}

function toggleGameSound() {
    isMuted = !isMuted;
    localStorage.setItem('gameSoundMuted', isMuted);
    const btn = document.getElementById('soundToggle');
    if (btn) btn.textContent = isMuted ? '🔇' : '🔊';
    const settingsBtn = document.getElementById('gameSoundToggle');
    if (settingsBtn) settingsBtn.checked = !isMuted;
    if (isMuted) stopBackgroundMusic();
    if (!isMuted) {
        unlockAudio();
        playTone(600, 0.1, 'sine', 0.15);
    }
}

function speakGame(text) {
    if (isMuted) return;
    if (!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'en-IN';
    utterance.rate = 0.9;
    utterance.pitch = 1.1;
    const voices = window.speechSynthesis.getVoices();
    const voice = voices.find(v => v.lang.startsWith('en'));
    if (voice) utterance.voice = voice;
    window.speechSynthesis.speak(utterance);
}

function playTone(freq, duration, type, volume) {
    if (isMuted) return;
    try {
        const ctx = getAudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = type || 'sine';
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(volume || 0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + duration);
    } catch(e) {}
}

function playClickSound() {
    playTone(800, 0.08, 'sine', 0.12);
}

function playMatchSound() {
    playTone(523, 0.12, 'sine', 0.2);
    setTimeout(() => playTone(659, 0.12, 'sine', 0.2), 120);
    setTimeout(() => playTone(784, 0.18, 'sine', 0.2), 240);
}

function playWrongSound() {
    playTone(200, 0.25, 'sawtooth', 0.12);
    setTimeout(() => playTone(150, 0.35, 'sawtooth', 0.12), 180);
}

function playLevelUpSound() {
    playTone(523, 0.12, 'sine', 0.18);
    setTimeout(() => playTone(659, 0.12, 'sine', 0.18), 130);
    setTimeout(() => playTone(784, 0.12, 'sine', 0.18), 260);
    setTimeout(() => playTone(1047, 0.25, 'sine', 0.2), 390);
}

function playWinSound() {
    const notes = [523, 659, 784, 1047, 784, 1047, 1319];
    notes.forEach((note, i) => {
        setTimeout(() => playTone(note, 0.18, 'sine', 0.18), i * 120);
    });
}

function playFlipSound() {
    playTone(440, 0.07, 'sine', 0.12);
}

function playStartVoice() {
    unlockAudio();
    speakGame("Let's start the game! Good luck!");
}

function playWinVoice(score) {
    const messages = [
        "Hurray! You won! That's amazing!",
        "That's great! Excellent performance!",
        "Wonderful! You did it! Keep it up!",
        "Fantastic! You're doing great!",
        "Brilliant! What a wonderful memory!"
    ];
    const msg = messages[Math.floor(Math.random() * messages.length)];
    speakGame(msg);
}

function playLoseVoice() {
    const messages = [
        "Good try! Let's play again!",
        "Almost there! Try once more!",
        "Nice effort! You can do it!"
    ];
    const msg = messages[Math.floor(Math.random() * messages.length)];
    speakGame(msg);
}

function startBackgroundMusic() {
    if (isMuted) return;
    unlockAudio();
    try {
        const ctx = getAudioContext();
        const melody = [262, 294, 330, 349, 392, 349, 330, 294];
        let noteIndex = 0;

        function playNote() {
            if (isMuted) return;
            try {
                const freq = melody[noteIndex % melody.length];
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gain.gain.setValueAtTime(0.05, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.45);
                noteIndex++;
            } catch(e) {}
        }

        playNote();
        bgMusicInterval = setInterval(playNote, 500);
    } catch(e) {}
}

function stopBackgroundMusic() {
    if (bgMusicInterval) {
        clearInterval(bgMusicInterval);
        bgMusicInterval = null;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('soundToggle');
    if (btn) btn.textContent = isMuted ? '🔇' : '🔊';
});
