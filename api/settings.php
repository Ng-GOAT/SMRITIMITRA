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
    case 'get_settings':
        $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = $result->fetch_assoc();

        if (!$settings) {
            $ins = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
            $ins->bind_param("i", $user_id);
            $ins->execute();
            $stmt->execute();
            $settings = $stmt->get_result()->fetch_assoc();
        }

        echo json_encode(['settings' => $settings]);
        break;

    case 'update_settings':
        $fields = ['medicine_reminders', 'activity_reminders', 'hydration_reminders', 'appointment_reminders',
                    'voice_companion', 'large_text', 'dark_mode'];
        $updates = [];
        $values = [];
        $types = '';

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $updates[] = "$field = ?";
                $values[] = intval($_POST[$field]);
                $types .= 'i';
            }
        }

        if (isset($_POST['preferred_language'])) {
            $updates[] = "preferred_language = ?";
            $values[] = sanitize($_POST['preferred_language']);
            $types .= 's';
        }

        if (!empty($updates)) {
            $values[] = $user_id;
            $types .= 'i';
            $sql = "UPDATE user_settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
        }

        echo json_encode(['success' => true]);
        break;

    case 'update_profile':
        $full_name = sanitize($_POST['full_name'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $dob = sanitize($_POST['dob'] ?? '');
        $language = sanitize($_POST['language'] ?? 'English');

        $stmt = $conn->prepare("UPDATE users SET full_name=?, age=?, dob=?, preferred_language=? WHERE id=?");
        $stmt->bind_param("sissi", $full_name, $age, $dob, $language, $user_id);
        $stmt->execute();

        $_SESSION['user_name'] = $full_name;
        echo json_encode(['success' => true]);
        break;

    case 'get_profile':
        $user = getCurrentUser();
        echo json_encode(['profile' => $user]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
