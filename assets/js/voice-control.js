// SmritiMitra Voice Control System
// Uses Web Speech API for voice recognition and synthesis

var VoiceControl = {
    recognition: null,
    isListening: false,
    isSupported: false,
    onResult: null,
    commands: {},
    menuMode: false,
    menuItems: [],
    menuCallback: null,

    init: function() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.log('Speech recognition not supported');
            return;
        }

        this.isSupported = true;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-IN';
        this.recognition.maxAlternatives = 1;

        var self = this;
        this.recognition.onresult = function(event) {
            var transcript = event.results[0][0].transcript.toLowerCase().trim();
            var confidence = event.results[0][0].confidence;
            self.handleVoiceInput(transcript, confidence);
        };

        this.recognition.onend = function() {
            self.isListening = false;
            self.updateMicButton();
        };

        this.recognition.onerror = function(event) {
            console.log('Speech error:', event.error);
            self.isListening = false;
            self.updateMicButton();
            if (event.error === 'not-allowed') {
                self.speak('Microphone access denied. Please allow microphone access.');
            }
        };

        this.registerDefaultCommands();
        this.createMicButton();
    },

    createMicButton: function() {
        if (document.getElementById('voiceMicBtn')) return;

        var btn = document.createElement('div');
        btn.id = 'voiceMicBtn';
        btn.innerHTML = '<div class="mic-icon">&#x1F3A4;</div><div class="mic-status">Tap to speak</div>';
        btn.onclick = function() { VoiceControl.toggle(); };
        btn.style.cssText = 'position:fixed;bottom:30px;right:30px;z-index:99998;cursor:pointer;text-align:center;';

        var style = document.createElement('style');
        style.textContent = `
            #voiceMicBtn {
                width: 70px; height: 70px;
                background: linear-gradient(135deg, #6d5dfc, #8b5cf6);
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 6px 25px rgba(109,93,252,0.4);
                transition: all 0.3s ease;
            }
            #voiceMicBtn:hover { transform: scale(1.1); }
            #voiceMicBtn.listening {
                background: linear-gradient(135deg, #dc2626, #ef4444);
                box-shadow: 0 6px 25px rgba(220,38,38,0.4);
                animation: micPulse 1s infinite;
            }
            #voiceMicBtn .mic-icon { font-size: 28px; filter: none; }
            #voiceMicBtn .mic-status {
                position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%);
                font-size: 11px; color: #64748b; white-space: nowrap; font-weight: 600;
            }
            #voiceMicBtn.listening .mic-status { color: #dc2626; }
            @keyframes micPulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.4); }
                50% { box-shadow: 0 0 0 15px rgba(220,38,38,0); }
            }
            #voiceCommandFeedback {
                position: fixed; bottom: 110px; right: 20px; z-index: 99998;
                background: white; padding: 12px 20px; border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.15); font-size: 14px;
                max-width: 300px; display: none;
                border-left: 4px solid #6d5dfc;
            }
            #voiceCommandFeedback.show { display: block; animation: slideUp 0.3s ease; }
            @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        `;
        document.head.appendChild(style);

        var feedback = document.createElement('div');
        feedback.id = 'voiceCommandFeedback';

        document.body.appendChild(btn);
        document.body.appendChild(feedback);
    },

    updateMicButton: function() {
        var btn = document.getElementById('voiceMicBtn');
        if (!btn) return;
        if (this.isListening) {
            btn.classList.add('listening');
            btn.querySelector('.mic-status').textContent = 'Listening...';
        } else {
            btn.classList.remove('listening');
            btn.querySelector('.mic-status').textContent = 'Tap to speak';
        }
    },

    toggle: function() {
        if (this.isListening) {
            this.stop();
        } else {
            this.start();
        }
    },

    start: function() {
        if (!this.isSupported) {
            this.speak('Voice control is not supported in this browser.');
            return;
        }
        try {
            this.recognition.start();
            this.isListening = true;
            this.updateMicButton();
            this.showFeedback('Listening...');
            this.speak('Yes, I am listening.');
        } catch(e) {
            console.log('Recognition error:', e);
        }
    },

    stop: function() {
        if (this.recognition) {
            this.recognition.stop();
        }
        this.isListening = false;
        this.updateMicButton();
    },

    speak: function(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-IN';
        utterance.rate = 0.9;
        utterance.pitch = 1.1;
        window.speechSynthesis.speak(utterance);
    },

    showFeedback: function(text) {
        var fb = document.getElementById('voiceCommandFeedback');
        if (!fb) return;
        fb.textContent = text;
        fb.classList.add('show');
        clearTimeout(this._feedbackTimeout);
        this._feedbackTimeout = setTimeout(function() {
            fb.classList.remove('show');
        }, 4000);
    },

    handleVoiceInput: function(transcript, confidence) {
        this.showFeedback('Heard: "' + transcript + '"');

        if (this.menuMode && this.menuItems.length > 0) {
            this.handleMenuInput(transcript);
            return;
        }

        for (var key in this.commands) {
            var cmd = this.commands[key];
            for (var i = 0; i < cmd.phrases.length; i++) {
                if (transcript.includes(cmd.phrases[i])) {
                    this.showFeedback('Executing: ' + cmd.description);
                    cmd.action(transcript);
                    return;
                }
            }
        }

        this.speak('Sorry, I did not understand. Say "help" for available commands.');
    },

    registerCommand: function(name, phrases, description, action) {
        this.commands[name] = {
            phrases: phrases,
            description: description,
            action: action
        };
    },

    registerDefaultCommands: function() {
        var self = this;

        this.registerCommand('help', ['help', 'what can you do', 'commands', 'options'],
            'Shows available commands', function() {
                self.speak('Available commands: Open games, Open medicines, Open memories, Open exercises, Open companion, Open caregiver, Open profile, Open settings, Go home, Go back, Read page, Stop speaking, Emergency call.');
            });

        this.registerCommand('home', ['go home', 'open home', 'dashboard', 'home'],
            'Navigate to dashboard', function() {
                self.speak('Opening dashboard');
                window.location.href = '/SmritiMitra/index.php';
            });

        this.registerCommand('games', ['open games', 'games', 'play games', 'cognitive games', 'start game'],
            'Navigate to games', function() {
                self.speak('Opening cognitive games');
                window.location.href = '/SmritiMitra/pages/games.php';
            });

        this.registerCommand('medicines', ['open medicines', 'medicines', 'my medicines', 'medicine'],
            'Navigate to medicines', function() {
                self.speak('Opening medicines');
                window.location.href = '/SmritiMitra/pages/medicines.php';
            });

        this.registerCommand('memories', ['open memories', 'memories', 'memory journey', 'memory'],
            'Navigate to memories', function() {
                self.speak('Opening memory journey');
                window.location.href = '/SmritiMitra/pages/memories.php';
            });

        this.registerCommand('exercises', ['open exercises', 'exercises', 'exercise'],
            'Navigate to exercises', function() {
                self.speak('Opening exercises');
                window.location.href = '/SmritiMitra/pages/exercises.php';
            });

        this.registerCommand('companion', ['open companion', 'companion', 'ai companion', 'chat'],
            'Navigate to AI companion', function() {
                self.speak('Opening AI companion');
                window.location.href = '/SmritiMitra/pages/companion.php';
            });

        this.registerCommand('caregiver', ['open caregiver', 'caregiver'],
            'Navigate to caregiver', function() {
                self.speak('Opening caregiver dashboard');
                window.location.href = '/SmritiMitra/pages/caregiver.php';
            });

        this.registerCommand('profile', ['open profile', 'profile', 'my profile'],
            'Navigate to profile', function() {
                self.speak('Opening profile');
                window.location.href = '/SmritiMitra/pages/profile.php';
            });

        this.registerCommand('settings', ['open settings', 'settings'],
            'Navigate to settings', function() {
                self.speak('Opening settings');
                window.location.href = '/SmritiMitra/pages/settings.php';
            });

        this.registerCommand('emergency', ['emergency', 'emergency call', 'call help', 'help me', 'sos'],
            'Start emergency call', function() {
                self.speak('Starting emergency call');
                window.location.href = '/SmritiMitra/pages/video-call.php';
            });

        this.registerCommand('stop', ['stop', 'stop speaking', 'quiet', 'silence', 'shut up'],
            'Stop speaking', function() {
                window.speechSynthesis.cancel();
                self.showFeedback('Stopped speaking');
            });

        this.registerCommand('read', ['read page', 'read this', 'what is on screen', 'read'],
            'Read page content', function() {
                var main = document.querySelector('.main-content');
                if (main) {
                    var text = main.innerText.substring(0, 500);
                    self.speak(text);
                }
            });

        this.registerCommand('back', ['go back', 'back', 'previous page'],
            'Go back', function() {
                window.history.back();
            });

        this.registerCommand('logout', ['logout', 'sign out', 'log out'],
            'Logout', function() {
                self.speak('Logging out');
                window.location.href = '/SmritiMitra/pages/logout.php';
            });
    },

    startMenuMode: function(items, callback) {
        this.menuMode = true;
        this.menuItems = items;
        this.menuCallback = callback;

        var menuText = 'Menu: ';
        for (var i = 0; i < items.length; i++) {
            menuText += (i + 1) + '. ' + items[i].name + '. ';
        }
        menuText += 'Say the number or name to select.';
        this.speak(menuText);
        this.showFeedback('Menu mode active - speak a number or name');
    },

    handleMenuInput: function(transcript) {
        var num = parseInt(transcript);
        if (!isNaN(num) && num >= 1 && num <= this.menuItems.length) {
            var item = this.menuItems[num - 1];
            this.speak('Selected: ' + item.name);
            this.menuMode = false;
            this.menuItems = [];
            if (this.menuCallback) this.menuCallback(item);
            return;
        }

        for (var i = 0; i < this.menuItems.length; i++) {
            if (transcript.includes(this.menuItems[i].name.toLowerCase())) {
                this.speak('Selected: ' + this.menuItems[i].name);
                this.menuMode = false;
                this.menuItems = [];
                if (this.menuCallback) this.menuCallback(this.menuItems[i]);
                return;
            }
        }

        this.speak('Please say a number from 1 to ' + this.menuItems.length + ' or the item name.');
    },

    exitMenuMode: function() {
        this.menuMode = false;
        this.menuItems = [];
        this.menuCallback = null;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    VoiceControl.init();
});
