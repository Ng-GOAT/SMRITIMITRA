const routineActivities = [
    { id: 1, icon: "🌅", name: "Wake Up", order: 1 },
    { id: 2, icon: "🪥", name: "Brush Teeth", order: 2 },
    { id: 3, icon: "🍳", name: "Breakfast", order: 3 },
    { id: 4, icon: "💊", name: "Take Medicine", order: 4 },
    { id: 5, icon: "🚶", name: "Morning Walk", order: 5 }
];
let selectedActivities = [], routineScore = 0, routineStarted = false;

function startRoutineGame() {
    stopBackgroundMusic();
    selectedActivities = []; routineStarted = true;
    document.getElementById("routineInstruction").textContent = "Select the activities in the correct daily order.";
    document.getElementById("selectedRoutine").textContent = "Choose activities in the correct order.";
    document.getElementById("routineProgress").textContent = "0 / 5";
    const routineItems = document.getElementById("routineItems");
    routineItems.innerHTML = "";
    const shuffledActivities = [...routineActivities].sort(() => Math.random() - 0.5);
    shuffledActivities.forEach(activity => {
        const button = document.createElement("button");
        button.classList.add("routine-item");
        button.innerHTML = `<span class="routine-icon">${activity.icon}</span><span>${activity.name}</span>`;
        button.onclick = () => selectActivity(activity, button);
        routineItems.appendChild(button);
    });
    playStartVoice();
    setTimeout(() => startBackgroundMusic(), 1500);
}

function selectActivity(activity, button) {
    if (!routineStarted) return;
    if (selectedActivities.some(item => item.id === activity.id)) return;
    playClickSound();
    selectedActivities.push(activity);
    button.classList.add("selected"); button.disabled = true;
    updateSelectedRoutine();
    document.getElementById("routineProgress").textContent = `${selectedActivities.length} / 5`;
    if (selectedActivities.length === routineActivities.length) checkRoutine();
}

function updateSelectedRoutine() {
    document.getElementById("selectedRoutine").innerHTML = selectedActivities
        .map((activity, index) => `<div class="selected-routine-item"><span>${index + 1}</span>${activity.icon} ${activity.name}</div>`).join("");
}

function clearRoutine() {
    if (!routineStarted) return;
    selectedActivities = [];
    document.getElementById("selectedRoutine").textContent = "Choose activities in the correct order.";
    document.getElementById("routineProgress").textContent = "0 / 5";
    document.querySelectorAll(".routine-item").forEach(button => { button.classList.remove("selected"); button.disabled = false; });
}

function checkRoutine() {
    routineStarted = false;
    stopBackgroundMusic();
    let correctCount = 0;
    selectedActivities.forEach((activity, index) => { if (activity.order === index + 1) correctCount++; });
    routineScore += correctCount * 10;
    document.getElementById("routineScore").textContent = routineScore;
    const instruction = document.getElementById("routineInstruction");
    if (correctCount === 5) {
        playWinSound();
        playWinVoice();
        instruction.textContent = "Excellent! You remembered the complete routine correctly!";
    } else {
        if (correctCount >= 3) playLevelUpSound();
        else playWrongSound();
        playLoseVoice();
        instruction.textContent = `You placed ${correctCount} out of 5 activities correctly. Try again!`;
    }
    const accuracy = Math.round((correctCount / 5) * 100);
    fetch('/SmritiMitra/api/games.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'save_score', game_type: 'routine_challenge', score: routineScore, level: 1, difficulty: correctCount >= 4 ? 'easy' : 'medium', accuracy: accuracy })
    }).catch(() => {});
}
