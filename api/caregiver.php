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
    case 'get_links':
        $stmt = $conn->prepare("SELECT cl.*, u.full_name as caregiver_name FROM caregiver_links cl JOIN users u ON cl.caregiver_id = u.id WHERE cl.patient_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $caregivers = [];
        while ($row = $result->fetch_assoc()) {
            $caregivers[] = $row;
        }
        echo json_encode(['caregivers' => $caregivers]);
        break;

    case 'link_caregiver':
        $caregiver_email = sanitize($_POST['caregiver_email'] ?? '');
        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ? AND role = 'caregiver'");
        $stmt->bind_param("s", $caregiver_email);
        $stmt->execute();
        $caregiver = $stmt->get_result()->fetch_assoc();

        if (!$caregiver) {
            http_response_code(404);
            echo json_encode(['error' => 'Caregiver not found']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM caregiver_links WHERE patient_id = ? AND caregiver_id = ?");
        $stmt->bind_param("ii", $user_id, $caregiver['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['error' => 'Already linked']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO caregiver_links (patient_id, caregiver_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $caregiver['id']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'caregiver_name' => $caregiver['full_name']]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to link']);
        }
        break;

    case 'unlink':
        $link_id = intval($_POST['link_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM caregiver_links WHERE id = ? AND patient_id = ?");
        $stmt->bind_param("ii", $link_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
