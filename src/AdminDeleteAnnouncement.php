<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in as admin.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $announcement_id = (int)$_POST['id'];

    // Check if the announcement exists
    $stmt = $conn->prepare("SELECT announcement_id FROM afpro_announcement WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Delete the announcement
    $stmt = $conn->prepare("DELETE FROM afpro_announcement WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting announcement: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request or missing ID.']);
}
?>