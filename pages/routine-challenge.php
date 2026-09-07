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

    <title>Daily Routine Challenge | SmritiMitra</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <?php include "../includes/header.php"; ?>


        <!-- PAGE HEADER -->
        <section class="game-page-header">

            <div>
                <p class="section-tag">COGNITIVE TRAINING</p>

                <h1>📅 Daily Routine Challenge</h1>

                <p>
                    Arrange the activities in the correct daily order.
                </p>
            </div>

            <div style="display:flex;align-items:center;gap:10px;">
                <a href="games.php" class="back-link">
                    ← Back to Games
                </a>
                <button id="soundToggle" onclick="toggleGameSound()" style="padding:10px 16px;background:#6d5dfc;color:white;border:none;border-radius:10px;font-size:20px;cursor:pointer;">🔊</button>
            </div>

        </section>


        <!-- STATS -->
        <div class="routine-stats">

            <div class="routine-stat-card">
                <span>🎯 Score</span>
                <strong id="routineScore">0</strong>
            </div>

            <div class="routine-stat-card">
                <span>📊 Progress</span>
                <strong id="routineProgress">0 / 5</strong>
            </div>

        </div>


        <!-- GAME -->
        <div class="routine-game-container">

            <p id="routineInstruction">
                Click Start Game to begin.
            </p>


            <div id="routineItems" class="routine-items">
                <!-- Activities will appear here -->
            </div>


            <div class="selected-order">

                <h3>Your Daily Routine</h3>

                <div id="selectedRoutine" class="selected-routine">
                    Choose activities in the correct order.
                </div>

            </div>


            <div class="routine-actions">

                <button
                    class="start-routine-btn"
                    onclick="startRoutineGame()"
                >
                    ▶ Start Game
                </button>

                <button
                    class="clear-routine-btn"
                    onclick="clearRoutine()"
                >
                    Clear
                </button>

            </div>

        </div>

    </main>

</div>


<script src="../assets/js/audio.js"></script>
<script src="../assets/js/routine-challenge.js"></script>

</body>

</html>