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
    case 'notify_caregiver':
        $caregiver_user_id = intval($_POST['caregiver_user_id'] ?? 0);

        $stmt = $conn->prepare("SELECT cl.*, u.full_name as caregiver_name FROM caregiver_links cl JOIN users u ON cl.caregiver_id = u.id WHERE cl.patient_id = ? AND cl.caregiver_id = ?");
        $stmt->bind_param("ii", $user_id, $caregiver_user_id);
        $stmt->execute();
        $link = $stmt->get_result()->fetch_assoc();

        if (!$link) {
            http_response_code(403);
            echo json_encode(['error' => 'No link found']);
            exit;
        }

        $patient_name = $user['full_name'] ?? 'Patient';

        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'emergency_call', ?)");
        $desc = "Emergency video call initiated by " . $patient_name;
        $stmt->bind_param("is", $user_id, $desc);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'emergency_alert', ?)");
        $alert_desc = "EMERGENCY: " . $patient_name . " is trying to reach you via video call!";
        $stmt->bind_param("is", $caregiver_user_id, $alert_desc);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Caregiver notified']);
        break;

    case 'get_emergency_alerts':
        $stmt = $conn->prepare("SELECT * FROM activity_log WHERE user_id = ? AND activity_type = 'emergency_alert' ORDER BY created_at DESC LIMIT 10");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        echo json_encode(['alerts' => $alerts]);
        break;

    case 'acknowledge_alert':
        $alert_id = intval($_POST['alert_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM activity_log WHERE id = ? AND user_id = ? AND activity_type = 'emergency_alert'");
        $stmt->bind_param("ii", $alert_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
