<?php
session_start();
require_once 'DBconnection.php';

header('Content-Type: application/json; charset=UTF-8');

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit feedback.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $event_id = $_POST['event_id'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $rating = $_POST['rating'] ?? null;

    if (empty($user_id) || empty($event_id) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Rating is required
    if ($rating === '' || $rating === null) {
        echo json_encode(['success' => false, 'message' => 'Rating is required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO afpro_feedback (user_id, event_id, message, rating) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("iisi", $user_id, $event_id, $message, $rating);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit;
?> 