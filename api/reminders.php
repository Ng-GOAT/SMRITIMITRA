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
    case 'get_reminders':
        $stmt = $conn->prepare("SELECT * FROM reminders WHERE user_id = ? AND reminder_time >= NOW() ORDER BY reminder_time ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reminders = [];
        while ($row = $result->fetch_assoc()) {
            $reminders[] = $row;
        }
        echo json_encode(['reminders' => $reminders]);
        break;

    case 'add_reminder':
        $type = sanitize($_POST['reminder_type'] ?? 'medicine');
        $title = sanitize($_POST['title'] ?? '');
        $time = sanitize($_POST['reminder_time'] ?? '');
        $recurring = intval($_POST['is_recurring'] ?? 0);
        $pattern = sanitize($_POST['recurrence_pattern'] ?? '');

        $stmt = $conn->prepare("INSERT INTO reminders (user_id, reminder_type, title, reminder_time, is_recurring, recurrence_pattern) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $type, $title, $time, $recurring, $pattern);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add reminder']);
        }
        break;

    case 'complete_reminder':
        $rem_id = intval($_POST['reminder_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE reminders SET is_completed = 1 WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $rem_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'delete_reminder':
        $rem_id = intval($_POST['reminder_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM reminders WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $rem_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
