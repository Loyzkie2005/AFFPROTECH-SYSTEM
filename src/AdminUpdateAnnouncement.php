<?php
session_start();
require_once 'DBconnection.php'; // Use the correct DB connection file

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $announcement_id = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : 0;
    $message = trim($_POST['message'] ?? '');
    $created_by = trim($_POST['created_by'] ?? '');

    if ($announcement_id <= 0 || strlen($message) < 10 || empty($created_by)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input. Message must be at least 10 characters.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE afpro_announcement SET message = ?, created_by = ? WHERE announcement_id = ?");
    $stmt->bind_param("ssi", $message, $created_by, $announcement_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Announcement updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating announcement: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
?>
