let audioCtx = null;
let bgMusicInterval = null;
let isMuted = localStorage.getItem('gameSoundMuted') === 'true';

function getAudioContext() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
}

function playClickSound() {
    playTone(800, 0.08, 'sine', 0.1);
}

function playMatchSound() {
    playTone(523, 0.1, 'sine', 0.15);
    setTimeout(() => playTone(659, 0.1, 'sine', 0.15), 100);
    setTimeout(() => playTone(784, 0.15, 'sine', 0.15), 200);
}

function playWrongSound() {
    playTone(200, 0.2, 'sawtooth', 0.08);
    setTimeout(() => playTone(150, 0.3, 'sawtooth', 0.08), 150);
}

function playLevelUpSound() {
    playTone(523, 0.12, 'sine', 0.12);
    setTimeout(() => playTone(659, 0.12, 'sine', 0.12), 120);
    setTimeout(() => playTone(784, 0.12, 'sine', 0.12), 240);
    setTimeout(() => playTone(1047, 0.2, 'sine', 0.15), 360);
}

function playWinSound() {
    const notes = [523, 659, 784, 1047, 784, 1047, 1319];
    notes.forEach((note, i) => {
        setTimeout(() => playTone(note, 0.15, 'sine', 0.12), i * 100);
    });
}

function playFlipSound() {
    playTone(440, 0.06, 'sine', 0.08);
}

function playStartVoice() {
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
    const ctx = getAudioContext();
    const melody = [262, 294, 330, 349, 392, 349, 330, 294];
    let noteIndex = 0;

    function playNote() {
        if (isMuted) return;
        const freq = melody[noteIndex % melody.length];
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(0.04, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.4);
        noteIndex++;
    }

    playNote();
    bgMusicInterval = setInterval(playNote, 500);
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
