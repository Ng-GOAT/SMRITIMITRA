<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pattern Recognition | SmritiMitra</title>

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


        <!-- PAGE HEADER -->
        <section class="game-page-header">

            <div>
                <p class="section-tag">COGNITIVE TRAINING</p>

                <h1>🧩 Pattern Recognition</h1>

                <p>
                    Look carefully at the pattern and choose the correct answer.
                </p>
            </div>

            <a href="games.php" class="back-link">
                ← Back to Games
            </a>

        </section>


        <!-- GAME STATS -->
        <div class="pattern-stats">

            <div class="pattern-stat-card">
                <span>🏆 Level</span>
                <strong id="patternLevel">1</strong>
            </div>

            <div class="pattern-stat-card">
                <span>🎯 Score</span>
                <strong id="patternScore">0</strong>
            </div>

        </div>


        <!-- GAME AREA -->
        <div class="pattern-game-container">

            <p id="patternInstruction">
                Press Start to begin the challenge.
            </p>


            <!-- PATTERN -->
            <div id="patternDisplay" class="pattern-display">
                ?
            </div>


            <!-- OPTIONS -->
            <div id="patternOptions" class="pattern-options">

                <button class="pattern-option">?</button>
                <button class="pattern-option">?</button>
                <button class="pattern-option">?</button>

            </div>


            <!-- START BUTTON -->
            <div class="pattern-actions">

                <button
                    class="start-pattern-btn"
                    onclick="startPatternGame()"
                >
                    ▶ Start Game
                </button>

            </div>

        </div>

    </main>

</div>


<script src="../assets/js/pattern-recognition.js"></script>

</body>

</html>