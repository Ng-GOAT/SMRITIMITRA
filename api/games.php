<?php
session_start();
include "../config/db.php";
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'save_score':
        $game_type = sanitize($_POST['game_type'] ?? '');
        $score = intval($_POST['score'] ?? 0);
        $level = intval($_POST['level'] ?? 1);
        $difficulty = sanitize($_POST['difficulty'] ?? 'easy');
        $accuracy = floatval($_POST['accuracy'] ?? 0);

        $stmt = $conn->prepare("INSERT INTO game_scores (user_id, game_type, score, level_reached, difficulty, accuracy) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiisd", $user_id, $game_type, $score, $level, $difficulty, $accuracy);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save score']);
        }
        break;

    case 'get_scores':
        $stmt = $conn->prepare("SELECT * FROM game_scores WHERE user_id = ? ORDER BY completed_at DESC LIMIT 50");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = [];
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        echo json_encode(['scores' => $scores]);
        break;

    case 'get_performance':
        $perf = [];
        $games = ['memory_match', 'number_sequence', 'pattern_recognition', 'routine_challenge'];
        foreach ($games as $game) {
            $stmt = $conn->prepare("SELECT AVG(score) as avg_score, AVG(accuracy) as avg_accuracy, MAX(level_reached) as max_level, COUNT(*) as total_games FROM game_scores WHERE user_id = ? AND game_type = ?");
            $stmt->bind_param("is", $user_id, $game);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $perf[$game] = $row;
        }

        $total_games = 0;
        $total_score = 0;
        foreach ($perf as $g) {
            $total_games += $g['total_games'];
            $total_score += ($g['avg_score'] ?? 0) * $g['total_games'];
        }

        $overall = $total_games > 0 ? round($total_score / $total_games) : 0;

        echo json_encode([
            'performance' => $perf,
            'total_games' => $total_games,
            'overall_score' => $overall
        ]);
        break;

    case 'get_adaptive_difficulty':
        $game_type = sanitize($_GET['game_type'] ?? 'memory_match');

        $stmt = $conn->prepare("SELECT AVG(accuracy) as avg_acc, COUNT(*) as cnt FROM game_scores WHERE user_id = ? AND game_type = ? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stmt->bind_param("is", $user_id, $game_type);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $difficulty = 'easy';
        if ($row['cnt'] >= 3) {
            if ($row['avg_acc'] >= 80) $difficulty = 'hard';
            elseif ($row['avg_acc'] >= 50) $difficulty = 'medium';
        }

        echo json_encode(['difficulty' => $difficulty, 'avg_accuracy' => $row['avg_acc'] ?? 0, 'games_played' => $row['cnt'] ?? 0]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
