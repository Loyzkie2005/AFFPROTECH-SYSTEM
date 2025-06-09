<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$newFullName = trim($_POST['newFullName'] ?? '');
$user_id = $_SESSION['id'] ?? null;

if (!$user_id || strlen($newFullName) < 3) {
    echo json_encode(['success' => false, 'message' => 'Invalid name or user.']);
    exit;
}

// Split name into first and last (simple logic)
$parts = explode(' ', $newFullName, 2);
$first = $parts[0];
$last = $parts[1] ?? '';

$stmt = $conn->prepare("UPDATE USERS SET first_name = ?, last_name = ? WHERE user_id = ?");
$stmt->bind_param("ssi", $first, $last, $user_id);

if ($stmt->execute()) {
    $_SESSION["fullname"] = $newFullName;
    echo json_encode(['success' => true, 'message' => 'Name updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
$stmt->close();
$conn->close();
exit;
?>
