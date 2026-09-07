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
    case 'get_memories':
        $stmt = $conn->prepare("SELECT * FROM memories WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $memories = [];
        while ($row = $result->fetch_assoc()) {
            $memories[] = $row;
        }
        echo json_encode(['memories' => $memories]);
        break;

    case 'add_memory':
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $category = sanitize($_POST['category'] ?? 'other');
        $photo_url = '';

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/SmritiMitra/uploads/memories/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'mem_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $photo_url = '/SmritiMitra/uploads/memories/' . $filename;
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO memories (user_id, title, description, category, photo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $title, $description, $category, $photo_url);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id, 'photo_url' => $photo_url]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add memory']);
        }
        break;

    case 'toggle_favorite':
        $mem_id = intval($_POST['memory_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE memories SET is_favorite = NOT is_favorite WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $mem_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'delete_memory':
        $mem_id = intval($_POST['memory_id'] ?? 0);
        $stmt = $conn->prepare("SELECT photo_url FROM memories WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $mem_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['photo_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $row['photo_url'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $row['photo_url']);
            }
        }
        $stmt = $conn->prepare("DELETE FROM memories WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $mem_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
