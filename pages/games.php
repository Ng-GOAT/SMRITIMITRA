<?php
session_start();
include "../config/db.php";
requireLogin();
$user = getCurrentUser();

$pstmt = $conn->prepare("SELECT AVG(accuracy) as avg_acc FROM game_scores WHERE user_id=? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$pstmt->bind_param("i", $user['id']); $pstmt->execute();
$perf = $pstmt->get_result()->fetch_assoc();

$gstmt = $conn->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id=? AND DATE(completed_at)=CURDATE()");
$gstmt->bind_param("i", $user['id']); $gstmt->execute();
$games_today = $gstmt->get_result()->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cognitive Games | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include "../includes/sidebar.php"; ?>
    <main class="main-content">
        <?php include "../includes/header.php"; ?>

        <div class="page-heading">
            <div>
                <p class="page-label">COGNITIVE TRAINING</p>
                <h1>Cognitive Games 🧠</h1>
                <p>Fun activities designed to exercise memory, attention and recognition.</p>
            </div>
            <div style="display:flex;gap:10px;">
                <div class="medicine-date">🎯 Daily Goal: 3 Activities</div>
                <button onclick="generateReport()" style="padding:12px 20px;background:#6d5dfc;color:white;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-size:14px;">📄 Download Report</button>
            </div>
        </div>

        <section class="game-progress-overview">
            <div class="game-progress-text">
                <p class="summary-label">TODAY'S PROGRESS</p>
                <h2><?php echo min($games_today, 3); ?> of 3 Activities</h2>
                <p>Complete today's activities to keep your mind engaged.</p>
            </div>
            <div class="game-progress-bar"><div class="game-progress-fill" style="width:<?php echo min(($games_today/3)*100, 100); ?>%;"></div></div>
            <span><?php echo min(round(($games_today/3)*100), 100); ?>% Complete</span>
        </section>

        <section>
            <div class="section-title"><h2>Choose an Activity</h2><p>Select a game and enjoy your cognitive training.</p></div>
            <div class="games-grid">
                <a href="memory-match.php" class="game-card">
                    <div class="game-card-top"><div class="game-icon purple">🧠</div><span class="difficulty easy">Easy</span></div>
                    <h3>Memory Match</h3><p>Find matching pairs and strengthen your memory.</p>
                    <div class="game-card-footer"><span>⏱ 5 mins</span><strong>Play →</strong></div>
                </a>
                <a href="number-sequence.php" class="game-card">
                    <div class="game-card-top"><div class="game-icon blue">🔢</div><span class="difficulty medium">Medium</span></div>
                    <h3>Number Sequence</h3><p>Remember numbers and repeat the sequence.</p>
                    <div class="game-card-footer"><span>⏱ 3 mins</span><strong>Play →</strong></div>
                </a>
                <a href="pattern-recognition.php" class="game-card">
                    <div class="game-card-top"><div class="game-icon orange">👁️</div><span class="difficulty medium">Medium</span></div>
                    <h3>Pattern Recognition</h3><p>Identify patterns and find what comes next.</p>
                    <div class="game-card-footer"><span>⏱ 5 mins</span><strong>Play →</strong></div>
                </a>
                <a href="routine-challenge.php" class="game-card">
                    <div class="game-card-top"><div class="game-icon green">🖼️</div><span class="difficulty easy">Easy</span></div>
                    <h3>Object Recognition</h3><p>Recognize familiar objects and everyday items.</p>
                    <div class="game-card-footer"><span>⏱ 4 mins</span><strong>Play →</strong></div>
                </a>
            </div>
        </section>

        <section class="ai-performance">
            <div class="ai-performance-heading">
                <div><p class="page-label">AI PERFORMANCE INSIGHTS</p><h2>Your Cognitive Performance</h2><p>Your progress is tracked across different abilities.</p></div>
                <div class="ai-icon">🤖</div>
            </div>
            <div class="performance-grid">
                <div class="performance-item">
                    <div class="performance-label"><span>Memory</span><strong><?php echo round($perf['avg_acc'] ?? 72); ?>%</strong></div>
                    <div class="performance-bar"><div class="performance-fill fill-memory" style="width:<?php echo $perf['avg_acc'] ?? 72; ?>%;"></div></div>
                </div>
                <div class="performance-item">
                    <div class="performance-label"><span>Attention</span><strong><?php echo round(($perf['avg_acc'] ?? 81) * 1.1); ?>%</strong></div>
                    <div class="performance-bar"><div class="performance-fill fill-attention" style="width:<?php echo min(($perf['avg_acc'] ?? 81) * 1.1, 100); ?>%;"></div></div>
                </div>
                <div class="performance-item">
                    <div class="performance-label"><span>Recognition</span><strong><?php echo round(($perf['avg_acc'] ?? 65) * 0.9); ?>%</strong></div>
                    <div class="performance-bar"><div class="performance-fill fill-recognition" style="width:<?php echo ($perf['avg_acc'] ?? 65) * 0.9; ?>%;"></div></div>
                </div>
            </div>
            <div class="ai-game-insight">🤖 <strong>AI Insight:</strong> <?php echo ($perf['avg_acc'] ?? 0) > 70 ? 'Your performance is strong! Keep up the great work.' : 'Try playing more cognitive games to improve your performance.'; ?></div>
        </section>
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="../assets/js/pdf-report.js"></script>
</body>
</html>
