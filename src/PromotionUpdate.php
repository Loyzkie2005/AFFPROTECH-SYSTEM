<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

// Only allow admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in as admin.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $promotion_id = filter_input(INPUT_POST, 'promotion_id', FILTER_VALIDATE_INT);
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    // Validate inputs
    if (!$promotion_id || $promotion_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid promotion ID.']);
        exit;
    }
    if (!$title) {
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }
    if (!$description) {
        echo json_encode(['success' => false, 'message' => 'Description is required.']);
        exit;
    }
    if ($price === false || $price < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid price.']);
        exit;
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE afpro_promotion SET title = ?, description = ?, price = ? WHERE promotion_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: Failed to prepare statement.']);
        exit;
    }
    $stmt->bind_param("ssdi", $title, $description, $price, $promotion_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Promotion updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No promotion found with the provided ID or no changes made.']);
        }
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