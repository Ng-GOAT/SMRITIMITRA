function addMessage(message, sender) {
    const chatMessages = document.getElementById("chatMessages");
    const messageDiv = document.createElement("div");
    messageDiv.classList.add("chat-message");
    messageDiv.classList.add(sender === "user" ? "user-message" : "ai-message");
    const avatar = sender === "user" ? "👤" : "🧠";
    messageDiv.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content">${message}</div>
    `;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    if (sender === "ai" && isVoiceOutputEnabled()) {
        speakText(message);
    }
}

function isVoiceOutputEnabled() {
    const v = document.getElementById('voiceOutputToggle');
    if (v) return v.checked;
    return localStorage.getItem('voiceOutput') !== 'false';
}

function speakText(text) {
    if (!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const clean = text.replace(/[^\w\s.,!?]/g, '').trim();
    if (!clean) return;
    const utterance = new SpeechSynthesisUtterance(clean);
    utterance.lang = getVoiceLang();
    utterance.rate = 0.9;
    utterance.pitch = 1.0;
    const voices = window.speechSynthesis.getVoices();
    const langVoice = voices.find(v => v.lang.startsWith(utterance.lang.split('-')[0]));
    if (langVoice) utterance.voice = langVoice;
    window.speechSynthesis.speak(utterance);
}

function getVoiceLang() {
    const lang = localStorage.getItem('preferredLanguage') || 'English';
    const langMap = {
        'English': 'en-IN', 'Hindi': 'hi-IN', 'Assamese': 'as-IN', 'Bengali': 'bn-IN',
        'Manipuri': 'mni-IN', 'Mizo': 'en-IN', 'Nagamese': 'en-IN', 'Khasi': 'en-IN',
        'Garo': 'en-IN', 'Bodo': 'bn-IN', 'Tripuri': 'bn-IN', 'Marathi': 'mr-IN'
    };
    return langMap[lang] || 'en-IN';
}

const nerResponses = {
    'en': {
        medicine: "💊 Your next medicine is scheduled soon. Please take it on time for your health.",
        routine: "📅 Your daily routine includes medicine, cognitive activities, and rest. Stay consistent!",
        memory: "❤️ Visit Memory Journey to explore and preserve your beautiful memories.",
        help: "🤝 I'm here with you. You can talk to me, play a game, or explore your memories.",
        hello: "Hello! 👋 It's wonderful to talk with you. How can I help you today?",
        game: "🎮 Try playing a cognitive game today! Memory Match and Number Sequence are great for keeping your mind active.",
        water: "💧 Remember to drink water regularly. Staying hydrated is important for your health.",
        exercise: "🚶 A short walk can help improve your mood and keep you active. Try to walk for 10-15 minutes.",
        sleep: "😴 Getting proper sleep is important. Try to sleep at the same time every night.",
        fallback: "🧠 I'm here to help you with medicines, routines, memories, and cognitive activities. Try asking me something!"
    },
    'hi': {
        medicine: "💊 आपकी अगली दवाई जल्द ही है। कृपया समय पर लें।",
        routine: "📅 आपकी दैनिक दिनचर्या में दवाई, संज्ञानात्मक गतिविधियाँ और आराम शामिल हैं।",
        memory: "❤️ अपनी खूबसूरत यादें देखने के लिए स्मृति यात्रा पर जाएँ।",
        help: "🤝 मैं आपके साथ हूँ। आप मुझसे बात कर सकते हैं, खेल खेल सकते हैं।",
        hello: "नमस्ते! 👋 आपसे बात करके खुशी हुई। मैं आपकी कैसे मदद कर सकता हूँ?",
        fallback: "🧠 मैं दवाइयों, दिनचर्या, यादों और संज्ञानात्मक गतिविधियों में आपकी मदद करने के लिए हूँ।"
    },
    'as': {
        medicine: "💻 আপোনাৰ পৰৱৰ্তী ওষুধ শীঘ্ৰেই আছে। অনুগ্ৰহ কৰি সময়মতে লওক।",
        hello: "নমস্কাৰ! 👋 আপোনাৰ সৈতে কথা পাতি ভাল লাগিল।",
        fallback: "🧠 মই ওষুধ, দৈনিক দিনচৰ্যা, স্মৃতি আৰু সংজ্ঞানাত্মক কাৰ্যসূচীত সহায় কৰিবলৈ ইয়াত আছো।"
    }
};

function getAIResponse(message) {
    const input = message.toLowerCase();
    const lang = (localStorage.getItem('preferredLanguage') || 'English').toLowerCase();
    const langKey = lang.startsWith('hindi') ? 'hi' : lang.startsWith('assamese') ? 'as' : 'en';
    const responses = nerResponses[langKey] || nerResponses['en'];

    if (input.includes("medicine") || input.includes("dawai") || input.includes("dawa") || input.includes("ওষুধ") || input.includes("दवाई"))
        return responses.medicine;
    if (input.includes("schedule") || input.includes("routine") || input.includes("today") || input.includes("दिनचर्या"))
        return responses.routine;
    if (input.includes("memory") || input.includes("remember") || input.includes("याद") || input.includes("স্মৃতি"))
        return responses.memory;
    if (input.includes("help") || input.includes("sad") || input.includes("lonely") || input.includes("मदद") || input.includes("সহায়"))
        return responses.help;
    if (input.includes("hello") || input.includes("hi") || input.includes("namaste") || input.includes("নমস্কাৰ") || input.includes("नमस्ते"))
        return responses.hello;
    if (input.includes("game") || input.includes("play") || input.includes("खेल"))
        return responses.game || responses.fallback;
    if (input.includes("water") || input.includes("drink") || input.includes("पानी"))
        return responses.water || responses.fallback;
    if (input.includes("exercise") || input.includes("walk") || input.includes("सैर"))
        return responses.exercise || responses.fallback;
    if (input.includes("sleep") || input.includes("rest") || input.includes("नींद"))
        return responses.sleep || responses.fallback;

    return responses.fallback;
}

function sendMessage() {
    const input = document.getElementById("userMessage");
    const message = input.value.trim();
    if (message === "") return;
    addMessage(message, "user");
    input.value = "";
    setTimeout(() => {
        const response = getAIResponse(message);
        addMessage(response, "ai");
    }, 600);
}

function askQuestion(question) {
    document.getElementById("userMessage").value = question;
    sendMessage();
}

function handleEnter(event) {
    if (event.key === "Enter") sendMessage();
}

function startVoiceRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        addMessage("🎤 Voice recognition is not supported in this browser. Please try Google Chrome.", "ai");
        return;
    }
    const recognition = new SpeechRecognition();
    recognition.lang = getVoiceLang();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.onstart = function() { addMessage("🎤 Listening... Please speak.", "ai"); };
    recognition.onresult = function(event) {
        const speech = event.results[0][0].transcript;
        document.getElementById("userMessage").value = speech;
        sendMessage();
    };
    recognition.onerror = function() { addMessage("Sorry, I could not hear you. Please try again.", "ai"); };
    recognition.start();
}
