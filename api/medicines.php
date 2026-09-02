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
    case 'get_medicines':
        $stmt = $conn->prepare("SELECT * FROM medicines WHERE user_id = ? ORDER BY time_hour, time_minute");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $medicines = [];
        while ($row = $result->fetch_assoc()) {
            $medicines[] = $row;
        }
        echo json_encode(['medicines' => $medicines]);
        break;

    case 'add_medicine':
        $name = sanitize($_POST['name'] ?? '');
        $dosage = sanitize($_POST['dosage'] ?? '');
        $time_slot = sanitize($_POST['time_slot'] ?? 'morning');
        $time_hour = intval($_POST['time_hour'] ?? 8);
        $time_minute = intval($_POST['time_minute'] ?? 0);
        $period = sanitize($_POST['period'] ?? 'AM');

        $stmt = $conn->prepare("INSERT INTO medicines (user_id, name, dosage, time_slot, time_hour, time_minute, period) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiis", $user_id, $name, $dosage, $time_slot, $time_hour, $time_minute, $period);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add medicine']);
        }
        break;

    case 'take_medicine':
        $med_id = intval($_POST['medicine_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE medicines SET status='taken', taken_at=NOW() WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $med_id, $user_id);
        $stmt->execute();

        $log = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description, completed) VALUES (?, 'medicine', ?, 1)");
        $desc = "Took medicine #" . $med_id;
        $log->bind_param("is", $user_id, $desc);
        $log->execute();

        echo json_encode(['success' => true]);
        break;

    case 'delete_medicine':
        $med_id = intval($_POST['medicine_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $med_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'reset_daily':
        $stmt = $conn->prepare("UPDATE medicines SET status='pending', taken_at=NULL WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
