let notificationInterval = null;
let userSettings = {};
let notifiedReminders = {};
let notifiedMedicines = {};
let gameReminderInterval = null;
let lastGameScore = 0;

// --- On-Screen Reminder System ---
function createReminderBanner(title, message, icon, type) {
    const existing = document.getElementById('appReminderBanner');
    if (existing) existing.remove();

    const banner = document.createElement('div');
    banner.id = 'appReminderBanner';
    banner.style.cssText = 'position:fixed;top:20px;right:20px;max-width:380px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.15);z-index:99999;overflow:hidden;font-family:Inter,sans-serif;animation:slideIn 0.4s ease;';

    const colors = {
        medicine: '#22c55e',
        hydration: '#3b82f6',
        activity: '#f59e0b',
        appointment: '#8b5cf6',
        game: '#ec4899',
        default: '#6d5dfc'
    };
    const accent = colors[type] || colors.default;

    banner.innerHTML = `
        <div style="background:${accent};color:white;padding:10px 16px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:8px;">
            <span style="font-size:16px;">${icon || '🔔'}</span>
            <span>${title}</span>
            <button onclick="dismissReminder()" style="margin-left:auto;background:none;border:none;color:white;font-size:18px;cursor:pointer;">&times;</button>
        </div>
        <div style="padding:14px 16px;color:#1e293b;">
            <p style="font-size:14px;line-height:1.5;margin:0;">${message}</p>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button onclick="dismissReminder()" style="background:#f1f5f9;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;color:#475569;">Dismiss</button>
                <button onclick="goToPage('${type}')" style="background:${accent};color:white;border:none;border-radius:8px;padding:8px 16px;font-size:13px;cursor:pointer;">Go Now</button>
            </div>
        </div>
    `;

    document.body.appendChild(banner);

    const style = document.createElement('style');
    style.textContent = '@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}';
    document.head.appendChild(style);

    setTimeout(() => dismissReminder(), 15000);
}

function dismissReminder() {
    const b = document.getElementById('appReminderBanner');
    if (b) {
        b.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        b.style.transform = 'translateX(120%)';
        b.style.opacity = '0';
        setTimeout(() => b.remove(), 300);
    }
}

function goToPage(type) {
    dismissReminder();
    const pages = {
        medicine: '/SmritiMitra/pages/medicines.php',
        hydration: '/SmritiMitra/pages/settings.php',
        activity: '/SmritiMitra/pages/games.php',
        appointment: '/SmritiMitra/pages/settings.php',
        game: '/SmritiMitra/pages/games.php'
    };
    window.location.href = pages[type] || '/SmritiMitra/pages/games.php';
}

function showOnScreenMedicineReminder(med) {
    createReminderBanner(
        'Medicine Reminder',
        `Time to take your <strong>${med.name}</strong>!`,
        '💊',
        'medicine'
    );
}

function showOnScreenHydrationReminder() {
    createReminderBanner(
        'Hydration Reminder',
        'Time to drink some water! Stay hydrated.',
        '💧',
        'hydration'
    );
}

function showGameReminder() {
    fetch('/SmritiMitra/api/games.php?action=get_scores')
        .then(r => r.json())
        .then(data => {
            const scores = data.scores || [];
            const totalGames = scores.length;
            const now = new Date();
            let hoursSince = 25;

            if (totalGames > 0 && scores[0].completed_at) {
                const diff = (now - new Date(scores[0].completed_at)) / 3600000;
                hoursSince = diff;
            }

            if (hoursSince >= 24 || totalGames === 0) {
                const messages = [
                    "Keep your mind active! Try a cognitive game today.",
                    "A short game session helps maintain memory. Ready to play?",
                    "Brain training is important! Spend 5 minutes on a game.",
                    "Your daily brain exercise is waiting. Let's play!",
                    "Challenge yourself! A new game session keeps your mind sharp."
                ];
                const msg = messages[Math.floor(Math.random() * messages.length)];
                createReminderBanner(
                    'Game Reminder',
                    msg,
                    '🧠',
                    'game'
                );
            }
        })
        .catch(() => {});
}

function initNotifications() {
    if (!('Notification' in window)) {
        startOnScreenReminders();
        return;
    }

    fetch('/SmritiMitra/api/settings.php?action=get_settings')
        .then(r => r.json())
        .then(data => {
            userSettings = data.settings || {};
        })
        .catch(() => {});

    if (Notification.permission === 'default') {
        Notification.requestPermission().then(perm => {
            startReminderChecks();
            startOnScreenReminders();
        });
    } else if (Notification.permission === 'granted') {
        startReminderChecks();
        startOnScreenReminders();
    } else {
        startOnScreenReminders();
    }
}

function startOnScreenReminders() {
    showGameReminder();
    setInterval(showGameReminder, 600000);
}

function startReminderChecks() {
    checkReminders();
    checkMedicineReminders();
    checkHydrationReminder();
    showGameReminder();
    notificationInterval = setInterval(() => {
        checkReminders();
        checkMedicineReminders();
        checkHydrationReminder();
    }, 10000);
    gameReminderInterval = setInterval(showGameReminder, 600000);
}

function checkReminders() {
    if (Notification.permission !== 'granted') return;

    fetch('/SmritiMitra/api/reminders.php?action=get_reminders')
        .then(r => r.json())
        .then(data => {
            if (!data.reminders) return;
            const now = new Date();
            data.reminders.forEach(reminder => {
                if (reminder.is_completed) return;
                if (notifiedReminders[reminder.id]) return;
                const rTime = new Date(reminder.reminder_time);
                const diff = (rTime - now) / 60000;
                if (diff >= -2 && diff <= 5) {
                    showNotification(reminder);
                    notifiedReminders[reminder.id] = Date.now();
                }
            });
        })
        .catch(() => {});
}

function checkMedicineReminders() {
    if (Notification.permission !== 'granted') return;
    if (userSettings.medicine_reminders === '0') return;

    fetch('/SmritiMitra/api/medicines.php?action=get_medicines')
        .then(r => r.json())
        .then(data => {
            if (!data.medicines || data.medicines.length === 0) return;
            const now = new Date();
            const nowMinutes = now.getHours() * 60 + now.getMinutes();

            data.medicines.forEach(med => {
                if (med.status === 'taken') return;

                let h = parseInt(med.time_hour);
                let m = parseInt(med.time_minute);
                const period = (med.period || '').toUpperCase();

                if (period === 'PM' && h < 12) h += 12;
                if (period === 'AM' && h === 12) h = 0;

                const medMinutes = h * 60 + m;
                const diff = medMinutes - nowMinutes;

                if (diff >= -15 && diff <= 15) {
                    const key = med.id + '_' + now.toDateString();
                    if (!notifiedMedicines[key]) {
                        showMedicineNotification(med);
                        notifiedMedicines[key] = Date.now();
                    }
                }
            });
        })
        .catch(() => {});
}

function showMedicineNotification(med) {
    if (Notification.permission === 'granted') {
        new Notification('Medicine Reminder', {
            body: `Time to take your ${med.name}!`,
            icon: '/SmritiMitra/assets/icon-192.png',
            requireInteraction: true
        }).onclick = function() {
            window.focus();
            window.location.href = '/SmritiMitra/pages/medicines.php';
        };
    }
    showOnScreenMedicineReminder(med);
}

function checkHydrationReminder() {
    if (Notification.permission !== 'granted') return;
    if (userSettings.hydration_reminders === '0') return;

    const lastHydration = localStorage.getItem('lastHydration');
    const now = Date.now();
    if (lastHydration && (now - parseInt(lastHydration)) < 7200000) return;

    if (Notification.permission === 'granted') {
        new Notification('Hydration Reminder', {
            body: 'Time to drink some water! Stay hydrated.',
            icon: '/SmritiMitra/assets/icon-192.png',
            requireInteraction: true
        }).onclick = function() { window.focus(); };
    }
    showOnScreenHydrationReminder();
    localStorage.setItem('lastHydration', now.toString());
}

function showNotification(reminder) {
    if (Notification.permission === 'granted') {
        const icons = { medicine: 'Medicine', hydration: 'Water', activity: 'Activity', appointment: 'Appointment' };
        new Notification(`${icons[reminder.reminder_type] || 'Reminder'}: ${reminder.title}`, {
            body: `Scheduled for ${new Date(reminder.reminder_time).toLocaleTimeString()}`,
            icon: '/SmritiMitra/assets/icon-192.png',
            requireInteraction: true
        }).onclick = function() { window.focus(); };
    }
    const typeIcons = { medicine: '💊', hydration: '💧', activity: '🏃', appointment: '📅' };
    createReminderBanner(
        reminder.title,
        `Scheduled for ${new Date(reminder.reminder_time).toLocaleTimeString()}`,
        typeIcons[reminder.reminder_type] || '🔔',
        reminder.reminder_type
    );
}

function checkNotifications() {
    if (Notification.permission !== 'granted') {
        Notification.requestPermission().then(perm => {
            if (perm === 'granted') {
                alert('Notifications enabled! You will now receive reminders.');
                startReminderChecks();
            } else {
                alert('Notifications blocked. Please enable them in browser settings.');
            }
        });
    } else {
        fetch('/SmritiMitra/api/reminders.php?action=get_reminders')
            .then(r => r.json())
            .then(data => {
                const pending = (data.reminders || []).filter(r => !r.is_completed);
                alert(`You have ${pending.length} pending reminder(s).`);
            })
            .catch(() => {
                alert('Could not check reminders. Make sure you are logged in.');
            });
    }
}

function testNotification() {
    if (Notification.permission !== 'granted') {
        Notification.requestPermission().then(perm => {
            if (perm === 'granted') sendTestNotification();
            else alert('Please allow notifications in your browser!');
        });
    } else {
        sendTestNotification();
    }
}

function sendTestNotification() {
    new Notification('SmritiMitra Reminder Test', {
        body: 'Notifications are working! You will receive medicine, hydration, and activity reminders.',
        icon: '/SmritiMitra/assets/icon-192.png'
    });
}

function testMedicineReminder() {
    if (Notification.permission !== 'granted') {
        Notification.requestPermission().then(perm => {
            if (perm === 'granted') showMedicineReminderTest();
        });
    } else {
        showMedicineReminderTest();
    }
}

function showMedicineReminderTest() {
    new Notification('Medicine Reminder', {
        body: 'Time to take your evening medicine!',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    }).onclick = function() {
        window.focus();
        window.location.href = '/SmritiMitra/pages/medicines.php';
    };
}

function testHydrationReminder() {
    if (Notification.permission !== 'granted') {
        Notification.requestPermission().then(perm => {
            if (perm === 'granted') showHydrationReminderTest();
        });
    } else {
        showHydrationReminderTest();
    }
}

function showHydrationReminderTest() {
    new Notification('Hydration Reminder', {
        body: 'Time to drink some water! Stay hydrated.',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    }).onclick = function() { window.focus(); };
    localStorage.setItem('lastHydration', Date.now().toString());
}

document.addEventListener('DOMContentLoaded', initNotifications);
