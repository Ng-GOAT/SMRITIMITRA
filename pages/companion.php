<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Companion | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="companion-header">
            <div>
                <p class="section-tag">YOUR PERSONAL COMPANION</p>
                <h1>🎙️ Talk with SmritiMitra</h1>
                <p>I'm here to help you remember, stay organized, and feel connected.</p>
            </div>
            <div style="display:flex;align-items:center;gap:15px;">
                <label class="switch" title="Voice Output">
                    <input type="checkbox" id="voiceOutputToggle" checked onchange="localStorage.setItem('voiceOutput', this.checked)">
                    <span class="slider"></span>
                </label>
                <div class="companion-status"><span class="status-dot"></span>Online & Ready</div>
            </div>
        </section>

        <section class="companion-layout">
            <div class="companion-profile-card">
                <div class="ai-avatar">🧠</div>
                <h2>SmritiMitra AI</h2>
                <p>Your friendly cognitive care companion.</p>
                <div class="ai-status"><span></span>Ready to help</div>
                <div class="companion-features">
                    <div><span>💊</span> Medicine reminders</div>
                    <div><span>📅</span> Daily routine</div>
                    <div><span>❤️</span> Memory support</div>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-header">
                    <div><h3>Conversation</h3><p>Ask anything you need help remembering.</p></div>
                    <span>💬</span>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message ai-message">
                        <div class="message-avatar">🧠</div>
                        <div class="message-content">Hello! 👋 I'm SmritiMitra. How can I help you today?</div>
                    </div>
                </div>

                <div class="suggested-questions">
                    <button onclick="askQuestion('When is my next medicine?')">💊 When is my next medicine?</button>
                    <button onclick="askQuestion('What is my schedule today?')">📅 What is my schedule today?</button>
                    <button onclick="askQuestion('Tell me about my memories')">❤️ Help me remember</button>
                    <button onclick="askQuestion('I need a drink of water')">💧 Drink water</button>
                    <button onclick="askQuestion('Should I take a walk?')">🚶 Exercise tip</button>
                </div>

                <div class="chat-input-area">
                    <input type="text" id="userMessage" placeholder="Type your message..." onkeypress="handleEnter(event)">
                    <button class="voice-button" onclick="startVoiceRecognition()" title="Speak">🎤</button>
                    <button class="send-button" onclick="sendMessage()">Send →</button>
                </div>
            </div>
        </section>

        <section class="companion-quick-help">
            <div class="quick-help-title"><h2>How can I help you?</h2><p>Choose a topic to start a conversation.</p></div>
            <div class="quick-help-grid">
                <button onclick="askQuestion('Tell me about my medicine')"><span>💊</span><strong>Medicines</strong><small>Check reminders</small></button>
                <button onclick="askQuestion('Tell me about my routine')"><span>📅</span><strong>Daily Routine</strong><small>View today's activities</small></button>
                <button onclick="askQuestion('Help me remember my memories')"><span>❤️</span><strong>Memories</strong><small>Remember special moments</small></button>
                <button onclick="askQuestion('I need help')"><span>🤝</span><strong>Need Help?</strong><small>Talk with SmritiMitra</small></button>
            </div>
        </section>
    </main>
</div>
<script src="../assets/js/companion.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const v = localStorage.getItem('voiceOutput');
    document.getElementById('voiceOutputToggle').checked = v !== 'false';
});
</script>
</body>
</html>
