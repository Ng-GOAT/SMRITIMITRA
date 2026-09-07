<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();

$pstmt = $conn->prepare("SELECT AVG(accuracy) as avg_acc FROM game_scores WHERE user_id=? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$pstmt->bind_param("i", $user['id']); $pstmt->execute();
$perf = $pstmt->get_result()->fetch_assoc();

$mstmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='taken' THEN 1 ELSE 0 END) as taken FROM medicines WHERE user_id=?");
$mstmt->bind_param("i", $user['id']); $mstmt->execute();
$meds = $mstmt->get_result()->fetch_assoc();

$gstmt = $conn->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id=? AND DATE(completed_at)=CURDATE()");
$gstmt->bind_param("i", $user['id']); $gstmt->execute();
$games = $gstmt->get_result()->fetch_assoc()['cnt'];

$sstmt = $conn->prepare("SELECT COUNT(DISTINCT DATE(logged_at)) as s FROM activity_log WHERE user_id=? AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$sstmt->bind_param("i", $user['id']); $sstmt->execute();
$streak = $sstmt->get_result()->fetch_assoc()['s'];

$astmt = $conn->prepare("SELECT * FROM activity_log WHERE user_id=? ORDER BY logged_at DESC LIMIT 10");
$astmt->bind_param("i", $user['id']); $astmt->execute();
$activities = $astmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregiver Dashboard | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <section class="caregiver-header">
            <div>
                <p class="section-tag">CARE & PROGRESS MONITORING</p>
                <h1>&#x1F468;&#x200D;&#x1F469;&#x200D;&#x1F467; Caregiver Dashboard</h1>
                <p>Monitor daily activities, medicine adherence and cognitive progress.</p>
            </div>
            <button onclick="generateCaregiverReport()" style="padding:12px 20px;background:#6d5dfc;color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:14px;">&#x1F4C4; Download Report</button>
        </section>

        <section style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px;">
            <div style="background:linear-gradient(135deg,#f0f0ff,#e8e8ff);border:2px solid #c7d2fe;border-radius:18px;padding:24px;">
                <div style="font-size:36px;margin-bottom:10px;">&#x1F517;</div>
                <h3 style="font-size:18px;margin-bottom:5px;">Link with Caregiver</h3>
                <p style="font-size:13px;color:#64748b;margin-bottom:15px;">Enter your caregiver's email to connect. They will be able to monitor your progress.</p>
                <form id="linkCaregiverForm" style="display:flex;gap:10px;">
                    <input type="email" id="caregiverEmail" placeholder="Caregiver's email address" required style="flex:1;padding:12px 16px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;">
                    <button type="submit" style="padding:12px 24px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;">Link</button>
                </form>
                <div id="linkStatus" style="margin-top:10px;font-size:13px;font-weight:600;"></div>
            </div>

            <div style="background:white;border-radius:18px;padding:24px;box-shadow:0 5px 20px rgba(0,0,0,0.04);">
                <h3 style="font-size:18px;margin-bottom:15px;">&#x1F465; Linked Caregivers</h3>
                <div id="linkedCaregiversList">
                    <p style="color:#64748b;">Loading...</p>
                </div>
            </div>
        </section>

        <section class="patient-overview-card">
            <div class="patient-avatar-large"><?php echo $user['avatar'] ?? '👵'; ?></div>
            <div class="patient-overview-info">
                <p class="section-tag">PATIENT OVERVIEW</p>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p>View and monitor the patient's daily cognitive care progress.</p>
            </div>
            <div class="patient-status"><span class="status-dot"></span>Active Today</div>
        </section>

        <section class="caregiver-stats-grid">
            <div class="caregiver-stat-card">
                <div class="caregiver-stat-icon">🧠</div>
                <div><span>Cognitive Activity</span><strong><?php echo round($perf['avg_acc'] ?? 0); ?>%</strong><small>Based on last 7 days</small></div>
            </div>
            <div class="caregiver-stat-card">
                <div class="caregiver-stat-icon">💊</div>
                <div><span>Medicine Adherence</span><strong><?php echo ($meds['taken'] ?? 0) . ' / ' . ($meds['total'] ?? 0); ?></strong><small><?php echo ($meds['total'] ?? 0) - ($meds['taken'] ?? 0); ?> medicines remaining</small></div>
            </div>
            <div class="caregiver-stat-card">
                <div class="caregiver-stat-icon">🎮</div>
                <div><span>Activities Completed</span><strong><?php echo $games; ?></strong><small>Today's activities</small></div>
            </div>
            <div class="caregiver-stat-card">
                <div class="caregiver-stat-icon">🔥</div>
                <div><span>Engagement Streak</span><strong><?php echo $streak; ?> Days</strong><small>Keep it going!</small></div>
            </div>
        </section>

        <section class="performance-section">
            <div class="performance-heading">
                <div><h2>Cognitive Performance</h2><p>Based on recent activities and games.</p></div>
                <span class="performance-period">Last 7 Days</span>
            </div>
            <div class="performance-grid">
                <div class="performance-card"><div class="performance-top"><span>Memory</span><strong><?php echo round($perf['avg_acc'] ?? 0); ?>%</strong></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo $perf['avg_acc'] ?? 0; ?>%;"></div></div></div>
                <div class="performance-card"><div class="performance-top"><span>Attention</span><strong><?php echo min(round(($perf['avg_acc'] ?? 0) * 1.1), 100); ?>%</strong></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo min(($perf['avg_acc'] ?? 0) * 1.1, 100); ?>%;"></div></div></div>
                <div class="performance-card"><div class="performance-top"><span>Recognition</span><strong><?php echo round(($perf['avg_acc'] ?? 0) * 0.9); ?>%</strong></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo ($perf['avg_acc'] ?? 0) * 0.9; ?>%;"></div></div></div>
                <div class="performance-card"><div class="performance-top"><span>Consistency</span><strong><?php echo min($streak * 14, 100); ?>%</strong></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo min($streak * 14, 100); ?>%;"></div></div></div>
            </div>
        </section>

        <section class="caregiver-bottom-grid">
            <div class="activity-overview-card">
                <div class="card-heading"><h2>Today's Activities</h2><span>📅 Today</span></div>
                <?php while ($act = $activities->fetch_assoc()): ?>
                <div class="activity-item <?php echo $act['completed'] ? 'completed' : 'pending'; ?>">
                    <span class="activity-check"><?php echo $act['completed'] ? '✓' : '○'; ?></span>
                    <div><strong><?php echo ucfirst($act['activity_type']); ?></strong><p><?php echo htmlspecialchars($act['description'] ?? ''); ?></p></div>
                </div>
                <?php endwhile; ?>
                <?php if ($activities->num_rows === 0): ?>
                    <p style="color:#64748b;padding:15px 0;">No activities logged today.</p>
                <?php endif; ?>
            </div>

            <div class="ai-observation-card">
                <div class="ai-observation-icon">🤖</div>
                <div>
                    <p class="section-tag">AI INSIGHT</p>
                    <h2>Today's Observation</h2>
                    <p><?php echo ($perf['avg_acc'] ?? 0) > 70 ? 'Cognitive activity appears stable today. Performance is on track.' : 'Memory performance could benefit from additional engagement. Consider trying more cognitive games.'; ?></p>
                    <p class="observation-note">This is an activity insight and not a medical diagnosis.</p>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="../assets/js/pdf-report.js"></script>
<script>
document.getElementById('linkCaregiverForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var email = document.getElementById('caregiverEmail').value;
    var status = document.getElementById('linkStatus');
    status.textContent = 'Linking...';
    status.style.color = '#64748b';

    fetch('/SmritiMitra/api/caregiver.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'link_caregiver', caregiver_email: email })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.textContent = 'Linked with ' + data.caregiver_name + '!';
            status.style.color = '#22c55e';
            document.getElementById('caregiverEmail').value = '';
            loadLinkedCaregivers();
        } else {
            status.textContent = data.error || 'Failed to link';
            status.style.color = '#dc2626';
        }
    })
    .catch(function() {
        status.textContent = 'Error. Please try again.';
        status.style.color = '#dc2626';
    });
});

function loadLinkedCaregivers() {
    fetch('/SmritiMitra/api/caregiver.php?action=get_links')
    .then(r => r.json())
    .then(data => {
        var list = document.getElementById('linkedCaregiversList');
        var links = data.caregivers || [];
        if (links.length === 0) {
            list.innerHTML = '<p style="color:#64748b;">No caregivers linked yet. Enter their email above.</p>';
            return;
        }
        list.innerHTML = '';
        links.forEach(function(link) {
            var name = link.caregiver_name || 'Caregiver';
            var initial = name.charAt(0).toUpperCase();
            var item = document.createElement('div');
            item.className = 'linked-caregiver-item';
            item.innerHTML = '<div class="caregiver-avatar-sm">' + initial + '</div>' +
                '<div><strong>' + name + '</strong><p>Linked since ' + new Date(link.linked_at).toLocaleDateString() + '</p></div>' +
                '<button onclick="unlinkCaregiver(' + link.id + ')" class="unlink-btn">Remove</button>';
            list.appendChild(item);
        });
    })
    .catch(function() {
        document.getElementById('linkedCaregiversList').innerHTML = '<p style="color:#dc2626;">Could not load caregivers.</p>';
    });
}

function unlinkCaregiver(linkId) {
    if (!confirm('Remove this caregiver?')) return;
    fetch('/SmritiMitra/api/caregiver.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'unlink', link_id: linkId })
    })
    .then(r => r.json())
    .then(() => loadLinkedCaregivers());
}

window.onload = function() { loadLinkedCaregivers(); };
</script>
</body>
</html>
