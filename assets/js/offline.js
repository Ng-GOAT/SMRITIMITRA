if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/SmritiMitra/assets/js/sw.js')
        .then(reg => console.log('SW registered'))
        .catch(err => console.log('SW error', err));
}

window.addEventListener('online', () => {
    syncOfflineData();
});

window.addEventListener('offline', () => {
    document.body.classList.add('offline-mode');
});

function syncOfflineData() {
    const pending = JSON.parse(localStorage.getItem('offlineActions') || '[]');
    pending.forEach(action => {
        fetch(action.url, { method: action.method, body: action.body })
            .then(r => r.json())
            .catch(() => {});
    });
    localStorage.setItem('offlineActions', '[]');
    document.body.classList.remove('offline-mode');
}

function saveOfflineAction(url, method, body) {
    const pending = JSON.parse(localStorage.getItem('offlineActions') || '[]');
    pending.push({ url, method, body: body ? body.toString() : '' });
    localStorage.setItem('offlineActions', JSON.stringify(pending));
}

function saveSettingsLocal(settings) {
    localStorage.setItem('smritimitra_settings', JSON.stringify(settings));
}

function getSettingsLocal() {
    return JSON.parse(localStorage.getItem('smritimitra_settings') || '{}');
}

function applyOfflineSettings() {
    const s = getSettingsLocal();
    if (s.dark_mode) document.body.classList.add('dark-mode');
    if (s.large_text) document.body.classList.add('large-text');
}

document.addEventListener('DOMContentLoaded', applyOfflineSettings);
