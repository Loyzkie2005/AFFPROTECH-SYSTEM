<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    $created_by = $_SESSION['fullname'];
    $created_at = date('Y-m-d H:i:s');

    if (strlen($message) < 10) {
        echo json_encode(['success' => false, 'message' => 'Announcement message must be at least 10 characters long.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO afpro_announcement (message, created_by, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $message, $created_by, $created_at);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Announcement Created!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating announcement: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>
