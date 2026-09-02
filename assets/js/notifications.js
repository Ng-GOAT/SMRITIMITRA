let notificationInterval = null;

function initNotifications() {
    if (!('Notification' in window)) {
        console.log('Notifications not supported');
        return;
    }

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
    notificationInterval = setInterval(checkReminders, 30000);
    checkMedicineReminders();
    checkHydrationReminder();
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
    const medReminder = localStorage.getItem('medicineReminderShown');
    const today = new Date().toDateString();
    if (medReminder === today) return;

    fetch('/SmritiMitra/api/medicines.php?action=get_medicines')
        .then(r => r.json())
        .then(data => {
            if (!data.medicines) return;
            const now = new Date();
            data.medicines.forEach(med => {
                if (med.status === 'taken') return;
                let h = med.time_hour;
                if (med.period === 'PM' && h < 12) h += 12;
                if (med.period === 'AM' && h === 12) h = 0;
                const medTime = new Date();
                medTime.setHours(h, med.time_minute, 0, 0);
                const diff = (medTime - now) / 60000;
                if (diff > 0 && diff <= 15) {
                    showMedicineNotification(med);
                    localStorage.setItem('medicineReminderShown', today);
                }
            });
        })
        .catch(() => {});
}

function showMedicineNotification(med) {
    if (Notification.permission !== 'granted') return;
    const notif = new Notification('💊 Medicine Reminder', {
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
    const lastHydration = localStorage.getItem('lastHydration');
    const now = Date.now();
    if (lastHydration && (now - parseInt(lastHydration)) < 3600000) return;

    if (Notification.permission === 'granted') {
        const notif = new Notification('💧 Hydration Reminder', {
            body: 'Time to drink some water! Stay hydrated.',
            icon: '/SmritiMitra/assets/icon-192.png',
            requireInteraction: true
        });
        notif.onclick = function() { window.focus(); };
        localStorage.setItem('lastHydration', now.toString());
    }
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
    new Notification('✅ SmritiMitra Reminder Test', {
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
    const notif = new Notification('💊 Medicine Reminder', {
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
    const notif = new Notification('💧 Hydration Reminder', {
        body: 'Time to drink some water! Stay hydrated.',
        icon: '/SmritiMitra/assets/icon-192.png',
        requireInteraction: true
    });
    notif.onclick = function() { window.focus(); };
    localStorage.setItem('lastHydration', Date.now().toString());
}

document.addEventListener('DOMContentLoaded', initNotifications);
