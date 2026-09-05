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
    <title>My Profile | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="profile-header">
            <div>
                <p class="section-tag">PERSONAL PROFILE</p>
                <h1>👤 My Profile</h1>
                <p>Manage your personal information and care preferences.</p>
            </div>
            <div style="display:flex;gap:10px;">
                <button onclick="generateReport()" style="padding:12px 20px;background:#6d5dfc;color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:14px;">📄 Download Report</button>
                <button class="edit-profile-btn" onclick="toggleEditProfile()">✏️ Edit Profile</button>
            </div>
        </section>

        <section class="profile-hero-card">
            <div class="profile-avatar-large"><?php echo $user['avatar'] ?? '👤'; ?></div>
            <div class="profile-main-info">
                <h2 id="profileName"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="profile-role">SmritiMitra Care Member</p>
                <p class="profile-description">Your personal information helps us provide a more personalized cognitive care experience.</p>
            </div>
            <div class="profile-status"><span class="status-dot"></span>Account Active</div>
        </section>

        <section class="profile-content-grid">
            <div class="profile-info-card">
                <div class="profile-card-heading">
                    <div><p class="section-tag">ABOUT YOU</p><h2>Personal Information</h2></div>
                    <span class="profile-card-icon">👤</span>
                </div>
                <div id="profileView">
                    <div class="profile-details-grid">
                        <div class="profile-detail"><span>Full Name</span><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></div>
                        <div class="profile-detail"><span>Age</span><strong><?php echo $user['age'] ? $user['age'] . ' Years' : '-- Years'; ?></strong></div>
                        <div class="profile-detail"><span>Date of Birth</span><strong><?php echo $user['dob'] ? date('d M Y', strtotime($user['dob'])) : 'Not added'; ?></strong></div>
                        <div class="profile-detail"><span>Preferred Language</span><strong><?php echo $user['preferred_language']; ?></strong></div>
                    </div>
                </div>
                <div id="profileEdit" style="display:none;">
                    <form id="profileForm">
                        <div class="profile-details-grid">
                            <div class="profile-detail"><span>Full Name</span><input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" style="width:100%;padding:8px;border:2px solid #e2e8f0;border-radius:8px;margin-top:5px;box-sizing:border-box;"></div>
                            <div class="profile-detail"><span>Age</span><input type="number" name="age" value="<?php echo $user['age']; ?>" style="width:100%;padding:8px;border:2px solid #e2e8f0;border-radius:8px;margin-top:5px;box-sizing:border-box;"></div>
                            <div class="profile-detail"><span>Date of Birth</span><input type="date" name="dob" value="<?php echo $user['dob']; ?>" style="width:100%;padding:8px;border:2px solid #e2e8f0;border-radius:8px;margin-top:5px;box-sizing:border-box;"></div>
                            <div class="profile-detail"><span>Preferred Language</span><select name="language" style="width:100%;padding:8px;border:2px solid #e2e8f0;border-radius:8px;margin-top:5px;box-sizing:border-box;">
                                <?php foreach(['English','Hindi','Assamese','Bengali','Manipuri','Mizo','Nagamese','Khasi','Garo','Bodo','Tripuri','Marathi'] as $lang): ?>
                                    <option <?php echo $user['preferred_language'] === $lang ? 'selected' : ''; ?>><?php echo $lang; ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        </div>
                        <div style="display:flex;gap:10px;margin-top:15px;"><button type="submit" style="padding:10px 20px;background:#6d5dfc;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Save Changes</button><button type="button" onclick="toggleEditProfile()" style="padding:10px 20px;background:#f1f5f9;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Cancel</button></div>
                    </form>
                </div>
            </div>

            <div class="profile-info-card">
                <div class="profile-card-heading">
                    <div><p class="section-tag">PERSONALIZATION</p><h2>Care Preferences</h2></div>
                    <span class="profile-card-icon">⚙️</span>
                </div>
                <div class="preference-list">
                    <div class="preference-item"><div><strong>Medicine Reminders</strong><p>Get reminders for your daily medicines.</p></div><span class="preference-status">Enabled</span></div>
                    <div class="preference-item"><div><strong>Voice Companion</strong><p>Use voice to interact with SmritiMitra.</p></div><span class="preference-status">Enabled</span></div>
                    <div class="preference-item"><div><strong>Memory Activities</strong><p>Receive personalized cognitive activities.</p></div><span class="preference-status">Enabled</span></div>
                </div>
            </div>
        </section>

        <section class="profile-care-section">
            <div class="profile-care-card">
                <div class="profile-care-icon">👨‍👩‍👧</div>
                <div><h3>Caregiver Connection</h3><p>Connect a trusted family member or caregiver to help monitor your daily activities and progress.</p></div>
                <button class="connect-caregiver-btn">Connect Caregiver</button>
            </div>
            <div class="profile-care-card">
                <div class="profile-care-icon">🔐</div>
                <div><h3>Privacy & Security</h3><p>Manage how your information and activity data are used.</p></div>
                <button class="manage-privacy-btn">Manage Settings</button>
            </div>
        </section>
    </main>
</div>

<script>
function toggleEditProfile() {
    const v = document.getElementById('profileView');
    const e = document.getElementById('profileEdit');
    if (e.style.display === 'none') { v.style.display = 'none'; e.style.display = 'block'; }
    else { v.style.display = 'block'; e.style.display = 'none'; }
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('../api/settings.php', { method:'POST', body: new URLSearchParams({ action:'update_profile', full_name:fd.get('full_name'), age:fd.get('age'), dob:fd.get('dob'), language:fd.get('language') }) })
    .then(r=>r.json()).then(d => { if(d.success) location.reload(); });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="../assets/js/pdf-report.js"></script>
</body>
</html>
