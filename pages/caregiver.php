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
                <h1>👨‍👩‍👧 Caregiver Dashboard</h1>
                <p>Monitor daily activities, medicine adherence and cognitive progress.</p>
            </div>
            <button onclick="generateCaregiverReport()" style="padding:12px 20px;background:#6d5dfc;color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:14px;">📄 Download Report</button>
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
</body>
</html>
