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

            <a href="games.php" class="back-link">
                ← Back to Games
            </a>

        </section>


        <!-- GAME STATS -->
        <div class="game-stats">

            <div class="game-stat-card">
                <span>🎯 Moves</span>
                <strong id="moves">0</strong>
            </div>

            <div class="game-stat-card">
                <span>🧩 Matches</span>
                <strong id="matches">0 / 4</strong>
            </div>

        </div>


        <!-- GAME BOARD -->
        <div class="memory-game-container">

            <div class="memory-board" id="memoryBoard">
                <!-- Cards will be created by JavaScript -->
            </div>

        </div>


        <!-- RESTART -->
        <div class="game-actions">

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


<script src="../assets/js/memory-match.js"></script>

</body>

</html>