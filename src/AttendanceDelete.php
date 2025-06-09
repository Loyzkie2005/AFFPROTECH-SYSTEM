<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once 'DBconnection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $attendance_id = isset($_POST['attendance_id']) ? intval($_POST['attendance_id']) : 0;

    if (!$attendance_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid attendance ID']);
        exit;
    }

    // Delete the attendance record
    $stmt = $conn->prepare("DELETE FROM afpro_attendance WHERE attendance_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $attendance_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Attendance record deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete attendance record']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>