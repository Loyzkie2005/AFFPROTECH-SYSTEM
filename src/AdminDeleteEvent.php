<?php
header('Content-Type: application/json');
session_start();
require_once 'DBconnection.php';

// Permission check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the event ID from POST or GET
$event_id = $_POST['event_id'] ?? $_GET['event_id'] ?? null;
if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'No event ID provided']);
    exit;
}

// Delete logic
$stmt = $conn->prepare('DELETE FROM afpro_events WHERE event_id = ?');
$stmt->bind_param('i', $event_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting event.']);
}
$stmt->close();
$conn->close();
exit;
?>