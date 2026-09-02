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
    case 'get_dashboard':
        $today = date('Y-m-d');

        $med_stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='taken' THEN 1 ELSE 0 END) as taken FROM medicines WHERE user_id=?");
        $med_stmt->bind_param("i", $user_id);
        $med_stmt->execute();
        $meds = $med_stmt->get_result()->fetch_assoc();

        $game_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id=? AND DATE(completed_at)=?");
        $game_stmt->bind_param("is", $user_id, $today);
        $game_stmt->execute();
        $games_today = $game_stmt->get_result()->fetch_assoc()['cnt'];

        $streak_stmt = $conn->prepare("SELECT COUNT(DISTINCT DATE(logged_at)) as streak FROM activity_log WHERE user_id=? AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $streak_stmt->bind_param("i", $user_id);
        $streak_stmt->execute();
        $streak = $streak_stmt->get_result()->fetch_assoc()['streak'];

        $perf_stmt = $conn->prepare("SELECT AVG(accuracy) as avg_acc FROM game_scores WHERE user_id=? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $perf_stmt->bind_param("i", $user_id);
        $perf_stmt->execute();
        $perf = $perf_stmt->get_result()->fetch_assoc();

        echo json_encode([
            'medicines' => ['total' => $meds['total'] ?? 0, 'taken' => $meds['taken'] ?? 0],
            'games_today' => $games_today,
            'engagement_streak' => $streak,
            'cognitive_score' => round($perf['avg_acc'] ?? 0)
        ]);
        break;

    case 'get_caregiver_data':
        $patient_id = intval($_GET['patient_id'] ?? 0);
        if (!$patient_id) {
            $link = $conn->prepare("SELECT patient_id FROM caregiver_links WHERE caregiver_id=?");
            $link->bind_param("i", $user_id);
            $link->execute();
            $result = $link->get_result()->fetch_assoc();
            $patient_id = $result['patient_id'] ?? 0;
        }

        if (!$patient_id) {
            echo json_encode(['error' => 'No patient linked']);
            exit;
        }

        $today = date('Y-m-d');

        $med = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='taken' THEN 1 ELSE 0 END) as taken FROM medicines WHERE user_id=?");
        $med->bind_param("i", $patient_id);
        $med->execute();
        $meds = $med->get_result()->fetch_assoc();

        $game = $conn->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id=? AND DATE(completed_at)=?");
        $game->bind_param("is", $patient_id, $today);
        $game->execute();
        $games = $game->get_result()->fetch_assoc()['cnt'];

        $perf = $conn->prepare("SELECT AVG(accuracy) as avg_acc FROM game_scores WHERE user_id=? AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $perf->bind_param("i", $patient_id);
        $perf->execute();
        $p = $perf->get_result()->fetch_assoc();

        $streak = $conn->prepare("SELECT COUNT(DISTINCT DATE(logged_at)) as s FROM activity_log WHERE user_id=? AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $streak->bind_param("i", $patient_id);
        $streak->execute();
        $sk = $streak->get_result()->fetch_assoc();

        $activities = $conn->prepare("SELECT * FROM activity_log WHERE user_id=? ORDER BY logged_at DESC LIMIT 10");
        $activities->bind_param("i", $patient_id);
        $activities->execute();
        $act_result = $activities->get_result();
        $act_list = [];
        while ($row = $act_result->fetch_assoc()) {
            $act_list[] = $row;
        }

        $patient = $conn->prepare("SELECT full_name, age FROM users WHERE id=?");
        $patient->bind_param("i", $patient_id);
        $patient->execute();
        $pat_info = $patient->get_result()->fetch_assoc();

        echo json_encode([
            'patient' => $pat_info,
            'medicines' => ['total' => $meds['total'] ?? 0, 'taken' => $meds['taken'] ?? 0],
            'games_today' => $games,
            'cognitive_score' => round($p['avg_acc'] ?? 0),
            'engagement_streak' => $sk['s'] ?? 0,
            'activities' => $act_list,
            'patient_id' => $patient_id
        ]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
