<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Test | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .test-box { max-width:500px; margin:60px auto; background:white; padding:30px; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
        .test-box h2 { margin-bottom:20px; }
        .test-btn { display:block; width:100%; padding:15px; margin:10px 0; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer; color:white; }
        .test-btn.purple { background:#6d5dfc; }
        .test-btn.green { background:#10b981; }
        .test-btn.blue { background:#0ea5e9; }
        .test-btn.orange { background:#f59e0b; }
        .test-btn.red { background:#dc2626; }
        #log { margin-top:20px; padding:15px; background:#f1f5f9; border-radius:10px; font-family:monospace; font-size:13px; max-height:200px; overflow-y:auto; }
    </style>
</head>
<body>
<div class="test-box">
    <h2>🔊 Audio System Test</h2>
    <p>Click each button to test. Check console (F12) for errors.</p>
    
    <button class="test-btn purple" onclick="testUnlock()">1. Unlock Audio (Click First!)</button>
    <button class="test-btn green" onclick="testTone()">2. Test Tone</button>
    <button class="test-btn blue" onclick="testMatchSound()">3. Test Match Sound</button>
    <button class="test-btn orange" onclick="testWinSound()">4. Test Win Sound</button>
    <button class="test-btn red" onclick="testVoice()">5. Test Voice (TTS)</button>
    
    <div id="log"></div>
</div>

<script src="../assets/js/audio.js"></script>
<script>
function log(msg) {
    const el = document.getElementById('log');
    el.innerHTML += '<div>' + new Date().toLocaleTimeString() + ': ' + msg + '</div>';
    el.scrollTop = el.scrollHeight;
    console.log('[AudioTest]', msg);
}

function testUnlock() {
    unlockAudio();
    log('Audio unlocked. State: ' + (audioCtx ? audioCtx.state : 'no context'));
}

function testTone() {
    unlockAudio();
    playTone(440, 0.3, 'sine', 0.3);
    log('Tone played (440Hz). Muted: ' + isMuted);
}

function testMatchSound() {
    unlockAudio();
    playMatchSound();
    log('Match sound played');
}

function testWinSound() {
    unlockAudio();
    playWinSound();
    log('Win sound played');
}

function testVoice() {
    speakGame('Hello! This is a test of the voice system.');
    log('Voice test triggered. Muted: ' + isMuted);
}

log('Audio test page loaded. Muted: ' + isMuted + ', Unlocked: ' + audioUnlocked);
</script>
</body>
</html>
