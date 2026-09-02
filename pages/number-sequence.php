<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Number Sequence | SmritiMitra</title>

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

                <h1>🔢 Number Sequence</h1>

                <p>
                    Remember the sequence and repeat it correctly.
                </p>
            </div>

            <a href="games.php" class="back-link">
                ← Back to Games
            </a>

        </section>


        <!-- GAME INFORMATION -->

        <div class="sequence-stats">

            <div class="sequence-stat-card">
                <span>🏆 Level</span>
                <strong id="level">1</strong>
            </div>

            <div class="sequence-stat-card">
                <span>🎯 Score</span>
                <strong id="score">0</strong>
            </div>

        </div>


        <!-- GAME AREA -->

        <div class="sequence-game-container">

            <p id="gameInstruction">
                Press Start to begin the memory challenge.
            </p>


            <div id="sequenceDisplay" class="sequence-display">
                ?
            </div>


            <div id="numberButtons" class="number-buttons">

                <button onclick="addNumber(1)">1</button>
                <button onclick="addNumber(2)">2</button>
                <button onclick="addNumber(3)">3</button>
                <button onclick="addNumber(4)">4</button>
                <button onclick="addNumber(5)">5</button>

                <button onclick="addNumber(6)">6</button>
                <button onclick="addNumber(7)">7</button>
                <button onclick="addNumber(8)">8</button>
                <button onclick="addNumber(9)">9</button>

            </div>


            <div class="player-answer">

                <p>Your Answer:</p>

                <div id="userSequence" class="user-sequence">
                    -
                </div>

            </div>


            <div class="sequence-actions">

                <button
                    id="startButton"
                    class="start-sequence-btn"
                    onclick="startSequence()"
                >
                    ▶ Start Game
                </button>


                <button
                    class="clear-sequence-btn"
                    onclick="clearAnswer()"
                >
                    Clear
                </button>

            </div>

        </div>


    </main>

</div>

<script src="../assets/js/number-sequence.js"></script>

</body>

</html>