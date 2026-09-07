<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();

$sstmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id=?");
$sstmt->bind_param("i", $user['id']); $sstmt->execute();
$settings = $sstmt->get_result()->fetch_assoc();

if (!$settings) {
    $ins = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
    $ins->bind_param("i", $user['id']); $ins->execute();
    $sstmt->execute();
    $settings = $sstmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="settings-header">
            <div>
                <p class="section-tag">PREFERENCES</p>
                <h1>⚙️ Settings</h1>
                <p>Manage your SmritiMitra preferences.</p>
            </div>
        </section>

        <section class="settings-card">
            <h2>🔔 Notifications</h2>
            <div class="setting-row">
                <div><strong>Medicine Reminders</strong><p>Receive reminders for your medicines.</p></div>
                <label class="switch"><input type="checkbox" id="medReminder" <?php echo $settings['medicine_reminders'] ? 'checked' : ''; ?> onchange="saveSetting('medicine_reminders', this.checked)"><span class="slider"></span></label>
            </div>
            <div class="setting-row">
                <div><strong>Activity Reminders</strong><p>Get reminders for cognitive activities.</p></div>
                <label class="switch"><input type="checkbox" id="actReminder" <?php echo $settings['activity_reminders'] ? 'checked' : ''; ?> onchange="saveSetting('activity_reminders', this.checked)"><span class="slider"></span></label>
            </div>
            <div class="setting-row">
                <div><strong>Hydration Reminders</strong><p>Get reminded to drink water regularly.</p></div>
                <label class="switch"><input type="checkbox" id="hydReminder" <?php echo $settings['hydration_reminders'] ? 'checked' : ''; ?> onchange="saveSetting('hydration_reminders', this.checked)"><span class="slider"></span></label>
            </div>
        </section>

        <section class="settings-card">
            <h2>🎙️ Voice & Accessibility</h2>
            <div class="setting-row">
                <div><strong>Voice Companion</strong><p>Enable voice interaction with SmritiMitra.</p></div>
                <label class="switch"><input type="checkbox" id="voiceToggle" <?php echo $settings['voice_companion'] ? 'checked' : ''; ?> onchange="saveSetting('voice_companion', this.checked)"><span class="slider"></span></label>
            </div>
            <div class="setting-row">
                <div><strong>Large Text</strong><p>Use larger text for better readability.</p></div>
                <label class="switch"><input type="checkbox" id="largeTextToggle" <?php echo $settings['large_text'] ? 'checked' : ''; ?> onchange="saveSetting('large_text', this.checked); document.body.classList.toggle('large-text', this.checked)"><span class="slider"></span></label>
            </div>
            <div class="setting-row">
                <div><strong>Voice Output</strong><p>Enable text-to-speech for AI responses.</p></div>
                <label class="switch"><input type="checkbox" id="voiceOutputToggle" checked onchange="localStorage.setItem('voiceOutput', this.checked)"><span class="slider"></span></label>
            </div>
        </section>

        <section class="settings-card">
            <h2>🎨 Appearance</h2>
            <div class="setting-row">
                <div><strong>Dark Mode</strong><p>Switch between light and dark appearance.</p></div>
                <label class="switch"><input type="checkbox" id="darkModeToggle" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?> onchange="saveSetting('dark_mode', this.checked); document.body.classList.toggle('dark-mode', this.checked)"><span class="slider"></span></label>
            </div>
        </section>

        <section class="settings-card">
            <h2>🌐 Language</h2>
            <div class="setting-row">
                <div><strong>Preferred Language</strong><p>Select the language you prefer.</p></div>
                <select class="language-select" id="languageSelect" onchange="saveSetting('preferred_language', this.value); applyLanguage(this.value)">
                    <?php foreach(['English','Hindi','Assamese','Bengali','Manipuri','Mizo','Nagamese','Khasi','Garo','Bodo','Tripuri','Marathi'] as $lang): ?>
                        <option <?php echo $settings['preferred_language'] === $lang ? 'selected' : ''; ?>><?php echo $lang; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>

        <section class="settings-card">
            <h2>🔔 Test Reminders</h2>
            <p style="color:#64748b;font-size:13px;margin-bottom:15px;">Click to test if notifications are working.</p>
            <div class="setting-row">
                <div><strong>Test Notification</strong><p>Send a test notification to verify setup.</p></div>
                <button onclick="testNotification()" style="padding:10px 18px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;">Test Now</button>
            </div>
            <div class="setting-row">
                <div><strong>Test Medicine Reminder</strong><p>Simulate a medicine reminder.</p></div>
                <button onclick="testMedicineReminder()" style="padding:10px 18px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;">Test Medicine</button>
            </div>
            <div class="setting-row">
                <div><strong>Test Hydration Reminder</strong><p>Simulate a hydration reminder.</p></div>
                <button onclick="testHydrationReminder()" style="padding:10px 18px;background:#0ea5e9;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;">Test Water</button>
            </div>
        </section>

        <section class="settings-card danger-card">
            <h2>🔐 Account</h2>
            <div class="setting-row">
                <div><strong>Privacy & Security</strong><p>Manage your privacy and account preferences.</p></div>
                <button class="settings-action-btn">Manage</button>
            </div>
            <div class="setting-row">
                <div><strong>Sign Out</strong><p>Sign out of your SmritiMitra account.</p></div>
                <a href="logout.php" style="padding:10px 18px;background:#fef2f2;color:#dc2626;border-radius:10px;text-decoration:none;font-weight:600;">Sign Out</a>
            </div>
        </section>
    </main>
</div>

<script>
function saveSetting(field, value) {
    const data = new URLSearchParams({ action: 'update_settings' });
    data.append(field, value ? '1' : '0');
    fetch('../api/settings.php', { method: 'POST', body: data });
    const s = JSON.parse(localStorage.getItem('smritimitra_settings') || '{}');
    s[field] = value ? '1' : '0';
    localStorage.setItem('smritimitra_settings', JSON.stringify(s));
}

function applyLanguage(lang) {
    localStorage.setItem('preferredLanguage', lang);
    if (typeof window.i18nApply === 'function') window.i18nApply(lang);
}

document.addEventListener('DOMContentLoaded', function() {
    const d = document.getElementById('darkModeToggle');
    const l = document.getElementById('largeTextToggle');
    if (d && d.checked) document.body.classList.add('dark-mode');
    if (l && l.checked) document.body.classList.add('large-text');
});
</script>
</body>
</html>
