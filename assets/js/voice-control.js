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
    continuousMode: false,

    init: function() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.log('Speech recognition not supported');
            this.showFeedback('Voice not supported. Use Chrome or Edge browser.');
            return;
        }

        this.isSupported = true;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-US';
        this.recognition.maxAlternatives = 1;

        var self = this;
        this.recognition.onresult = function(event) {
            var transcript = event.results[0][0].transcript.toLowerCase().trim();
            var confidence = event.results[0][0].confidence;
            console.log('Voice heard:', transcript, 'confidence:', confidence);
            self.handleVoiceInput(transcript, confidence);
            if (self.continuousMode) {
                setTimeout(function() { self.start(); }, 300);
            }
        };

        this.recognition.onend = function() {
            console.log('Recognition ended');
            self.isListening = false;
            self.updateMicButton();
            if (self.continuousMode) {
                setTimeout(function() { self.start(); }, 500);
            }
        };

        this.recognition.onerror = function(event) {
            console.log('Speech error:', event.error);
            self.isListening = false;
            self.updateMicButton();
            if (event.error === 'not-allowed') {
                self.showFeedback('Microphone blocked. Allow mic in browser settings.');
            } else if (event.error === 'no-speech') {
                self.showFeedback('No speech heard. Tap mic and try again.');
            } else if (event.error === 'audio-capture') {
                self.showFeedback('No microphone found. Connect a mic.');
            } else {
                self.showFeedback('Error: ' + event.error);
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
            this.showFeedback('Voice not supported. Use Chrome or Edge browser.');
            return;
        }
        if (this.isListening) return;
        try {
            this.recognition.start();
            this.isListening = true;
            this.updateMicButton();
            if (!this.continuousMode) {
                this.showFeedback('Listening... Speak now!');
            }
        } catch(e) {
            console.log('Recognition error:', e);
            this.isListening = false;
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

        var bestMatch = null;
        var bestScore = 0;

        for (var key in this.commands) {
            var cmd = this.commands[key];
            for (var i = 0; i < cmd.phrases.length; i++) {
                var phrase = cmd.phrases[i];
                var score = this.calculateMatch(transcript, phrase);
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = cmd;
                }
            }
        }

        if (bestMatch && bestScore >= 0.5) {
            this.showFeedback('Executing: ' + bestMatch.description);
            bestMatch.action(transcript);
        } else {
            this.showFeedback('Command not recognized. Say "help" for options.');
        }
    },

    calculateMatch: function(transcript, phrase) {
        if (transcript === phrase) return 1.0;
        if (transcript.includes(phrase)) return 0.9;

        var tWords = transcript.split(' ');
        var pWords = phrase.split(' ');
        var matches = 0;

        for (var i = 0; i < pWords.length; i++) {
            for (var j = 0; j < tWords.length; j++) {
                if (tWords[j] === pWords[i]) {
                    matches++;
                    break;
                }
                if (this.levenshtein(tWords[j], pWords[i]) <= 2) {
                    matches += 0.7;
                    break;
                }
            }
        }

        return pWords.length > 0 ? matches / pWords.length : 0;
    },

    levenshtein: function(a, b) {
        if (a.length === 0) return b.length;
        if (b.length === 0) return a.length;
        var matrix = [];
        for (var i = 0; i <= b.length; i++) matrix[i] = [i];
        for (var j = 0; j <= a.length; j++) matrix[0][j] = j;
        for (var i = 1; i <= b.length; i++) {
            for (var j = 1; j <= a.length; j++) {
                if (b.charAt(i-1) === a.charAt(j-1)) {
                    matrix[i][j] = matrix[i-1][j-1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i-1][j-1] + 1,
                        matrix[i][j-1] + 1,
                        matrix[i-1][j] + 1
                    );
                }
            }
        }
        return matrix[b.length][a.length];
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

        this.registerCommand('help', ['help', 'what can you do', 'commands', 'options', 'what commands', 'tell me commands'],
            'Shows available commands', function() {
                self.speak('Available commands: Open games, Open medicines, Open memories, Open exercises, Open companion, Open caregiver, Open profile, Open settings, Go home, Go back, Read page, Stop speaking, Emergency call, Continuous mode, Stop listening.');
            });

        this.registerCommand('home', ['go home', 'open home', 'dashboard', 'home', 'go to home', 'open dashboard'],
            'Navigate to dashboard', function() {
                self.speak('Opening dashboard');
                window.location.href = '/SmritiMitra/index.php';
            });

        this.registerCommand('games', ['open games', 'games', 'play games', 'cognitive games', 'start game', 'go to games', 'open game'],
            'Navigate to games', function() {
                self.speak('Opening cognitive games');
                window.location.href = '/SmritiMitra/pages/games.php';
            });

        this.registerCommand('medicines', ['open medicines', 'medicines', 'my medicines', 'medicine', 'go to medicines', 'open medicine'],
            'Navigate to medicines', function() {
                self.speak('Opening medicines');
                window.location.href = '/SmritiMitra/pages/medicines.php';
            });

        this.registerCommand('memories', ['open memories', 'memories', 'memory journey', 'memory', 'go to memories', 'open memory'],
            'Navigate to memories', function() {
                self.speak('Opening memory journey');
                window.location.href = '/SmritiMitra/pages/memories.php';
            });

        this.registerCommand('exercises', ['open exercises', 'exercises', 'exercise', 'go to exercises', 'open exercise'],
            'Navigate to exercises', function() {
                self.speak('Opening exercises');
                window.location.href = '/SmritiMitra/pages/exercises.php';
            });

        this.registerCommand('companion', ['open companion', 'companion', 'ai companion', 'chat', 'go to companion', 'open ai'],
            'Navigate to AI companion', function() {
                self.speak('Opening AI companion');
                window.location.href = '/SmritiMitra/pages/companion.php';
            });

        this.registerCommand('caregiver', ['open caregiver', 'caregiver', 'go to caregiver', 'open care'],
            'Navigate to caregiver', function() {
                self.speak('Opening caregiver dashboard');
                window.location.href = '/SmritiMitra/pages/caregiver.php';
            });

        this.registerCommand('profile', ['open profile', 'profile', 'my profile', 'go to profile'],
            'Navigate to profile', function() {
                self.speak('Opening profile');
                window.location.href = '/SmritiMitra/pages/profile.php';
            });

        this.registerCommand('settings', ['open settings', 'settings', 'go to settings'],
            'Navigate to settings', function() {
                self.speak('Opening settings');
                window.location.href = '/SmritiMitra/pages/settings.php';
            });

        this.registerCommand('emergency', ['emergency', 'emergency call', 'call help', 'help me', 'sos', 'call emergency'],
            'Start emergency call', function() {
                self.speak('Starting emergency call');
                window.location.href = '/SmritiMitra/pages/video-call.php';
            });

        this.registerCommand('stop', ['stop', 'stop speaking', 'quiet', 'silence', 'shut up', 'be quiet'],
            'Stop speaking', function() {
                window.speechSynthesis.cancel();
                self.showFeedback('Stopped speaking');
            });

        this.registerCommand('back', ['go back', 'back', 'previous page', 'return', 'go to back'],
            'Go back', function() {
                window.history.back();
            });

        this.registerCommand('logout', ['logout', 'sign out', 'log out', 'log me out'],
            'Logout', function() {
                self.speak('Logging out');
                window.location.href = '/SmritiMitra/pages/logout.php';
            });

        this.registerCommand('continuous_on', ['continuous mode', 'always listen', 'keep listening', 'always on', 'stay on', 'start continuous', 'continuous on'],
            'Enable continuous listening mode', function() {
                self.continuousMode = true;
                self.showFeedback('Continuous mode ON - I will always listen');
                self.speak('Continuous mode activated. I will always listen to your commands.');
            });

        this.registerCommand('continuous_off', ['stop listening', 'continuous off', 'turn off', 'disable continuous', 'sleep', 'stop continuous'],
            'Disable continuous listening mode', function() {
                self.continuousMode = false;
                self.stop();
                self.showFeedback('Continuous mode OFF');
                self.speak('Continuous mode deactivated.');
            });

        this.registerCommand('go_back', ['go back', 'back', 'previous page', 'return'],
            'Go back to previous page', function() {
                window.history.back();
            });

        this.registerCommand('refresh', ['refresh', 'reload', 'reload page', 'refresh page'],
            'Refresh current page', function() {
                window.location.reload();
            });

        this.registerCommand('scroll_down', ['scroll down', 'go down', 'page down', 'down'],
            'Scroll down', function() {
                window.scrollBy(0, 300);
            });

        this.registerCommand('scroll_up', ['scroll up', 'go up', 'page up', 'up'],
            'Scroll up', function() {
                window.scrollBy(0, -300);
            });

        this.registerCommand('read_page', ['read page', 'read this', 'read screen', 'what is here', 'read aloud'],
            'Read page content aloud', function() {
                var main = document.querySelector('.main-content');
                if (main) {
                    var text = main.innerText.substring(0, 800);
                    self.speak(text);
                }
            });

        this.registerCommand('repeat', ['repeat', 'say again', 'what did you say', 'repeat that', 'say that again'],
            'Repeat last feedback', function() {
                var fb = document.getElementById('voiceCommandFeedback');
                if (fb && fb.textContent) {
                    self.speak(fb.textContent);
                }
            });

        this.registerCommand('yes', ['yes', 'yeah', 'confirm', 'ok', 'okay', 'sure', 'yep'],
            'Confirm action', function() {
                var confirmBtn = document.querySelector('.confirm-yes, [onclick*="confirm"]');
                if (confirmBtn) confirmBtn.click();
            });

        this.registerCommand('no', ['no', 'nope', 'cancel', 'never mind', 'forget it', 'nah'],
            'Cancel action', function() {
                var cancelBtn = document.querySelector('.confirm-no, [onclick*="cancel"]');
                if (cancelBtn) cancelBtn.click();
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
    // Don't auto-speak on page load - only speak when user taps mic
});
