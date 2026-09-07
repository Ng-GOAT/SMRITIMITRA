<?php
session_start();
include "../config/db.php";
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Memory Match | SmritiMitra</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app-layout">

    <!-- SIDEBAR -->
    <?php include "../includes/sidebar.php"; ?>


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- HEADER -->
        <?php include "../includes/header.php"; ?>


        <section class="game-page-header">

            <div>
                <p class="section-tag">COGNITIVE TRAINING</p>

                <h1>🧠 Memory Match</h1>

                <p>
                    Find matching pairs and strengthen your memory.
                </p>
            </div>

            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">
                    ← Back to Games
                </a>
                <button id="soundToggle" onclick="toggleGameSound()" style="padding:10px 16px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-size:20px;cursor:pointer;">🔊</button>
            </div>

        </section>


        <!-- GAME STATS -->
        <div class="game-stats" id="gameStats" style="display:none;">

            <div class="game-stat-card">
                <span>🎯 Moves</span>
                <strong id="moves">0</strong>
            </div>

            <div class="game-stat-card">
                <span>🧩 Matches</span>
                <strong id="matches">0 / 4</strong>
            </div>

        </div>

        <!-- START SCREEN -->
        <div id="startScreen" style="text-align:center;padding:60px 20px;">
            <div style="font-size:80px;margin-bottom:20px;">🧠</div>
            <h2 style="margin-bottom:10px;">Memory Match</h2>
            <p style="color:#64748b;margin-bottom:30px;">Find matching pairs to win! Listen for sounds and voice guidance.</p>
            <button onclick="startGame()" style="padding:18px 50px;background:linear-gradient(135deg,#6d5dfc,#8b5cf6);color:white;border:none;border-radius:14px;font-size:18px;font-weight:700;cursor:pointer;box-shadow:0 8px 25px rgba(109,93,252,0.3);">
                ▶ Start Game
            </button>
        </div>


        <!-- GAME BOARD -->
        <div class="memory-game-container" id="gameArea" style="display:none;">

            <div class="memory-board" id="memoryBoard">
            </div>

        </div>


        <!-- RESTART -->
        <div class="game-actions" id="gameActions" style="display:none;">

            <button class="restart-game-btn" onclick="startGame()">
                🔄 Restart Game
            </button>

        </div>


        <!-- RESULT -->
        <div id="gameResult" class="game-result hidden">

            <h2>🎉 Excellent!</h2>

            <p id="resultText"></p>

            <button class="restart-game-btn" onclick="startGame()">
                Play Again
            </button>

        </div>

    </main>

</div>


<script src="../assets/js/audio.js"></script>
<script src="../assets/js/memory-match.js"></script>

</body>

</html>