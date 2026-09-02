<?php
session_start();
include "config/db.php";
$user = getCurrentUser();
$today = date('Y-m-d');

$med_total = 0; $med_taken = 0; $games_today = 0; $cognitive = 0;
if ($user) {
    $m = $conn->prepare("SELECT COUNT(*) as t, SUM(CASE WHEN status='taken' THEN 1 ELSE 0 END) as tk FROM medicines WHERE user_id=?");
    $m->bind_param("i", $user['id']); $m->execute(); $mr = $m->get_result()->fetch_assoc();
    $med_total = $mr['t'] ?? 0; $med_taken = $mr['tk'] ?? 0;

    $g = $conn->prepare("SELECT COUNT(*) as c FROM game_scores WHERE user_id=? AND DATE(completed_at)=?");
    $g->bind_param("is", $user['id'], $today); $g->execute(); $games_today = $g->get_result()->fetch_assoc()['c'];

    $p = $conn->prepare("SELECT AVG(accuracy) as a FROM game_scores WHERE user_id=? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $p->bind_param("i", $user['id']); $p->execute(); $cognitive = round($p->get_result()->fetch_assoc()['a'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmritiMitra | AI Cognitive Care</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="assets/manifest.json">
    <meta name="theme-color" content="#6d5dfc">
</head>
<body>
<div class="app-layout">
    <?php include "includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "includes/header.php"; ?>

        <section class="hero-card">
            <div>
                <h2>Your mind deserves care, every day. 🧠</h2>
                <p>SmritiMitra is your AI-powered cognitive care companion, helping you stay engaged, remember daily routines, and stay connected with your loved ones.</p>
            </div>
            <div class="hero-brain">🧠</div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">🧠</span><span>↗</span></div>
                <div class="stat-value"><?php echo $cognitive; ?>%</div>
                <div class="stat-label">Cognitive Activity</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">💊</span><span>Today</span></div>
                <div class="stat-value"><?php echo $med_taken . '/' . max($med_total, 1); ?></div>
                <div class="stat-label">Medicines Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">🎮</span><span>Today</span></div>
                <div class="stat-value"><?php echo $games_today; ?></div>
                <div class="stat-label">Activities Completed</div>
            </div>
        </section>

        <section>
            <div class="section-title">
                <h2>Your Care Hub</h2>
                <p>Everything you need for cognitive care in one place.</p>
            </div>
            <div class="feature-grid">
                <a href="pages/games.php" class="feature-card">
                    <div class="feature-icon-box">🎮</div>
                    <h3>Cognitive Games</h3>
                    <p>Train memory, attention and recognition.</p>
                    <span class="feature-arrow">Explore →</span>
                </a>
                <a href="pages/memories.php" class="feature-card">
                    <div class="feature-icon-box">❤️</div>
                    <h3>Memory Journey</h3>
                    <p>Relive beautiful moments and memories.</p>
                    <span class="feature-arrow">Explore →</span>
                </a>
                <a href="pages/medicines.php" class="feature-card">
                    <div class="feature-icon-box">💊</div>
                    <h3>My Medicines</h3>
                    <p>Stay on track with daily medicines.</p>
                    <span class="feature-arrow">View Schedule →</span>
                </a>
                <a href="pages/companion.php" class="feature-card">
                    <div class="feature-icon-box">🎙️</div>
                    <h3>AI Companion</h3>
                    <p>Talk with your personal memory companion.</p>
                    <span class="feature-arrow">Start Talking →</span>
                </a>
                <a href="pages/caregiver.php" class="feature-card">
                    <div class="feature-icon-box">👨‍👩‍👧</div>
                    <h3>Caregiver Dashboard</h3>
                    <p>Monitor activity and progress.</p>
                    <span class="feature-arrow">View Insights →</span>
                </a>
                <a href="pages/profile.php" class="feature-card">
                    <div class="feature-icon-box">👤</div>
                    <h3>My Profile</h3>
                    <p>Manage personal information and preferences.</p>
                    <span class="feature-arrow">Manage Profile →</span>
                </a>
            </div>
        </section>
    </main>
</div>
<script src="assets/js/settings.js"></script>
<script src="assets/js/notifications.js"></script>
<script src="assets/js/offline.js"></script>
<script src="assets/js/i18n.js"></script>
</body>
</html>
