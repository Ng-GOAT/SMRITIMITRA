<?php
session_start();
include "../config/db.php";
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_exercises':
        $category = sanitize($_GET['category'] ?? '');
        if ($category) {
            $stmt = $conn->prepare("SELECT * FROM exercises WHERE is_active = 1 AND category = ? ORDER BY created_at DESC");
            $stmt->bind_param("s", $category);
        } else {
            $stmt = $conn->prepare("SELECT * FROM exercises WHERE is_active = 1 ORDER BY category, created_at DESC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $exercises = [];
        while ($row = $result->fetch_assoc()) {
            $exercises[] = $row;
        }
        echo json_encode(['exercises' => $exercises]);
        break;

    case 'add_exercise':
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $video_url = sanitize($_POST['video_url'] ?? '');
        $category = sanitize($_POST['category'] ?? 'acupressure');
        $duration = intval($_POST['duration_minutes'] ?? 10);
        $difficulty = sanitize($_POST['difficulty'] ?? 'easy');

        $stmt = $conn->prepare("INSERT INTO exercises (title, description, video_url, category, duration_minutes, difficulty) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $title, $description, $video_url, $category, $duration, $difficulty);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add exercise']);
        }
        break;

    case 'update_exercise':
        $id = intval($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $video_url = sanitize($_POST['video_url'] ?? '');
        $category = sanitize($_POST['category'] ?? 'acupressure');
        $duration = intval($_POST['duration_minutes'] ?? 10);
        $difficulty = sanitize($_POST['difficulty'] ?? 'easy');

        $stmt = $conn->prepare("UPDATE exercises SET title=?, description=?, video_url=?, category=?, duration_minutes=?, difficulty=? WHERE id=?");
        $stmt->bind_param("sssssii", $title, $description, $video_url, $category, $duration, $difficulty, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update exercise']);
        }
        break;

    case 'delete_exercise':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM exercises WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'get_categories':
        $stmt = $conn->prepare("SELECT DISTINCT category, COUNT(*) as count FROM exercises WHERE is_active = 1 GROUP BY category");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(['categories' => $categories]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
