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

// Check if attendance ID is provided
if (!isset($_POST['attendance_id']) || !is_numeric($_POST['attendance_id'])) {
    $response['message'] = 'Invalid or missing attendance ID.';
    error_log('AttendanceUpdate: Invalid or missing attendance ID: ' . ($_POST['attendance_id'] ?? 'not set'));
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$attendance_id = (int)$_POST['attendance_id'];

// Retrieve and sanitize form inputs
$user_id = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$event_id = isset($_POST['event_id']) && is_numeric($_POST['event_id']) ? (int)$_POST['event_id'] : null;
$check_in_time = trim($_POST['check_in_time'] ?? '');
$check_out_time = trim($_POST['check_out_time'] ?? '');
$status = trim($_POST['status'] ?? ''); // Allow manual status override

// Basic validation
if (empty($user_id) || empty($event_id) || empty($status)) {
    $response['message'] = 'Please fill in all required fields.';
    error_log('AttendanceUpdate: Missing required fields - user_id: ' . ($user_id ?? 'null') . ', event_id: ' . ($event_id ?? 'null') . ', status: ' . ($status ?? 'null'));
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validate status
if (!in_array($status, ['Present', 'Absent'])) {
    $response['message'] = 'Invalid status value.';
    error_log('AttendanceUpdate: Invalid status value: ' . $status);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Validate user_id exists
$stmt = $conn->prepare("SELECT user_id FROM afpro_users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $response['message'] = 'Invalid user ID.';
    error_log('AttendanceUpdate: Invalid user ID: ' . $user_id);
    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
$stmt->close();

// Validate event_id exists
$stmt = $conn->prepare("SELECT event_id FROM afpro_events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $response['message'] = 'Invalid event ID.';
    error_log('AttendanceUpdate: Invalid event ID: ' . $event_id);
    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
$stmt->close();

// Validate check-in and check-out times if provided
if (!empty($check_in_time) && !empty($check_out_time)) {
    try {
        $check_in_dt = new DateTime($check_in_time);
        $check_out_dt = new DateTime($check_out_time);
        if ($check_out_dt <= $check_in_dt) {
            $response['message'] = 'Check-out time must be after check-in time.';
            error_log('AttendanceUpdate: Check-out time before check-in: ' . $check_in_time . ' vs ' . $check_out_time);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        $check_in_time = $check_in_dt->format('Y-m-d H:i:s');
        $check_out_time = $check_out_dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $response['message'] = 'Invalid date format.';
        error_log('AttendanceUpdate: Invalid date format: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
} else {
    // Allow null for optional check-in/check-out times
    $check_in_time = !empty($check_in_time) ? (new DateTime($check_in_time))->format('Y-m-d H:i:s') : null;
    $check_out_time = !empty($check_out_time) ? (new DateTime($check_out_time))->format('Y-m-d H:i:s') : null;
}

// Check if the attendance record exists
$stmt = $conn->prepare("SELECT attendance_id FROM afpro_attendance WHERE attendance_id = ?");
$stmt->bind_param("i", $attendance_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $response['message'] = 'Attendance record not found.';
    error_log('AttendanceUpdate: Attendance record not found for ID: ' . $attendance_id);
    $stmt->close();
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
$stmt->close();

// Use the provided status directly
$dynamic_status = $status;

// Prepare SQL statement to update the attendance record
$stmt = $conn->prepare("UPDATE afpro_attendance SET user_id = ?, event_id = ?, check_in_time = ?, check_out_time = ?, status = ? WHERE attendance_id = ?");
$stmt->bind_param("iisssi", $user_id, $event_id, $check_in_time, $check_out_time, $dynamic_status, $attendance_id);

// Execute the statement
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Attendance record updated successfully!';
    error_log('AttendanceUpdate: Successfully updated attendance ID: ' . $attendance_id);
} else {
    $response['message'] = 'Error updating attendance record: ' . $conn->error;
    error_log('AttendanceUpdate: Database error: ' . $conn->error);
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>