<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

// Only allow admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $promotion_id = $_POST['promotion_id'] ?? '';

    if (!$promotion_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid promotion ID.']);
        exit;
    }

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM afpro_promotion WHERE promotion_id = ?");
    $stmt->bind_param("i", $promotion_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promotion deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>