<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Include database connection
require_once 'DBconnection.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if the required fields are provided
if (!isset($_POST['event_id']) || !isset($_POST['eventTitle']) || !isset($_POST['eventStartDate']) || !isset($_POST['eventEndDate'])) {
    $response['message'] = 'Missing required fields.';
    error_log('AdminUpdateEvent: Missing required fields - POST data: ' . json_encode($_POST));
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$event_id = (int)$_POST['event_id'];
$title = trim($_POST['eventTitle']);
$description = trim($_POST['eventDescription'] ?? '');
$start_date = $_POST['eventStartDate'];
$end_date = $_POST['eventEndDate'];
$location = trim($_POST['eventLocation'] ?? '');

// Log received POST data for debugging
error_log('AdminUpdateEvent: Received - event_id: ' . $event_id . ', title: ' . $title . 
          ', start_date: ' . $start_date . ', end_date: ' . $end_date);

// Basic validation
if (empty($title) || empty($start_date) || empty($end_date)) {
    $response['message'] = 'Please fill in all required fields.';
    error_log('AdminUpdateEvent: Empty required fields - title: ' . $title . 
              ', start_date: ' . $start_date . ', end_date: ' . $end_date);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validate date format and ensure end date is after start date
try {
    $start_dt = new DateTime($start_date);
    $end_dt = new DateTime($end_date);
    if ($end_dt <= $start_dt) {
        $response['message'] = 'End date must be after start date.';
        error_log('AdminUpdateEvent: End date not after start date - start: ' . $start_date . 
                  ', end: ' . $end_date);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $start_date = $start_dt->format('Y-m-d H:i:s');
    $end_date = $end_dt->format('Y-m-d H:i:s');
} catch (Exception $e) {
    $response['message'] = 'Invalid date format.';
    error_log('AdminUpdateEvent: Invalid date format - ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if the event exists
$stmt = $conn->prepare("SELECT event_id FROM afpro_events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $response['message'] = 'Event not found. It may have been deleted. Please refresh the events list and try again.';
    error_log('AdminUpdateEvent: Event not found for ID: ' . $event_id . ', POST: ' . json_encode($_POST));
    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
$stmt->close();

// Prepare SQL statement to update the event
$stmt = $conn->prepare("UPDATE afpro_events SET title = ?, description = ?, start_date = ?, end_date = ?, location = ? WHERE event_id = ?");
$stmt->bind_param("sssssi", $title, $description, $start_date, $end_date, $location, $event_id);

// Execute the statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Event updated successfully!';
        error_log('AdminUpdateEvent: Successfully updated event ID: ' . $event_id);
    } else {
        $response['message'] = 'No changes made to the event. Please modify at least one field.';
        error_log('AdminUpdateEvent: No changes detected for event ID: ' . $event_id);
    }
} else {
    $response['message'] = 'Error updating event: ' . $conn->error;
    error_log('AdminUpdateEvent: Database error: ' . $conn->error);
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>