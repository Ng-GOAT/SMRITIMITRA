let notificationInterval = null;
let userSettings = {};

function initNotifications() {
    if (!('Notification' in window)) {
        console.log('Notifications not supported');
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
            if (perm === 'granted') {
                startReminderChecks();
            }
        });
    } else if (Notification.permission === 'granted') {
        startReminderChecks();
    }
}

function startReminderChecks() {
    checkReminders();
    checkMedicineReminders();
    checkHydrationReminder();
    notificationInterval = setInterval(() => {
        checkReminders();
        checkMedicineReminders();
        checkHydrationReminder();
    }, 30000);
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
                const rTime = new Date(reminder.reminder_time);
                const diff = (rTime - now) / 60000;
                if (diff > 0 && diff <= 5) {
                    showNotification(reminder);
                }
            });
        })
        .catch(() => {});
}

function checkMedicineReminders() {
    if (Notification.permission !== 'granted') return;
    if (userSettings.medicine_reminders === '0') return;

    const today = new Date().toDateString();
    const lastMedCheck = localStorage.getItem('lastMedCheck');
    if (lastMedCheck === today) return;

    fetch('/SmritiMitra/api/medicines.php?action=get_medicines')
        .then(r => r.json())
        .then(data => {
            if (!data.medicines || data.medicines.length === 0) return;
            const now = new Date();
            let shown = false;

            data.medicines.forEach(med => {
                if (med.status === 'taken' || shown) return;

                let h = parseInt(med.time_hour);
                let m = parseInt(med.time_minute);
                const period = (med.period || '').toUpperCase();

                if (period === 'PM' && h < 12) h += 12;
                if (period === 'AM' && h === 12) h = 0;

                const medTime = new Date();
                medTime.setHours(h, m, 0, 0);
                const diff = (medTime - now) / 60000;

                if (diff >= -30 && diff <= 15) {
                    showMedicineNotification(med);
                    shown = true;
                }
            });

            if (shown) {
                localStorage.setItem('lastMedCheck', today);
            }
        })
        .catch(() => {});
}

function showMedicineNotification(med) {
    if (Notification.permission !== 'granted') return;
    const notif = new Notification('Medicine Reminder', {
        body: `Time to take your ${med.name}!`,
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() {
        window.focus();
        window.location.href = '/SmritiMitra/pages/medicines.php';
    };
}

function checkHydrationReminder() {
    if (Notification.permission !== 'granted') return;
    if (userSettings.hydration_reminders === '0') return;

    const lastHydration = localStorage.getItem('lastHydration');
    const now = Date.now();
    if (lastHydration && (now - parseInt(lastHydration)) < 7200000) return;

    const notif = new Notification('Hydration Reminder', {
        body: 'Time to drink some water! Stay hydrated.',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() { window.focus(); };
    localStorage.setItem('lastHydration', now.toString());
}

function showNotification(reminder) {
    if (Notification.permission !== 'granted') return;
    const icons = { medicine: '💊', hydration: '💧', activity: '🎮', appointment: '🏥' };
    const notif = new Notification(`${icons[reminder.reminder_type] || '🔔'} ${reminder.title}`, {
        body: `Scheduled for ${new Date(reminder.reminder_time).toLocaleTimeString()}`,
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() { window.focus(); };
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
            if (perm === 'granted') {
                sendTestNotification();
            } else {
                alert('Please allow notifications in your browser!');
            }
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
    const notif = new Notification('Medicine Reminder', {
        body: 'Time to take your evening medicine!',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() {
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
    const notif = new Notification('Hydration Reminder', {
        body: 'Time to drink some water! Stay hydrated.',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() { window.focus(); };
    localStorage.setItem('lastHydration', Date.now().toString());
}

document.addEventListener('DOMContentLoaded', initNotifications);
