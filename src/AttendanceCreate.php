<?php
ob_start(); // Start output buffering to catch any unintended output
ini_set('display_errors', 0); // Disable displaying errors to the browser
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', 'php_errors.log'); // Log errors to a file

header('Content-Type: application/json; charset=UTF-8');
session_start();

date_default_timezone_set('Asia/Manila');

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once 'DBconnection.php';

// Verify database connection
if (!$conn) {
    ob_end_clean();
    error_log("Database connection failed: " . mysqli_connect_error());
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
        $check_in_time = trim($_POST['check_in_time'] ?? '');
        $check_out_time = trim($_POST['check_out_time'] ?? '');

        // Validate required fields
        if (!$user_id || !$event_id || empty($check_in_time)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        // Validate dates
        try {
            $check_in_dt = new DateTime($check_in_time);
            if (!empty($check_out_time)) {
                $check_out_dt = new DateTime($check_out_time);
                if ($check_out_dt <= $check_in_dt) {
                    ob_end_clean();
                    echo json_encode(['success' => false, 'message' => 'Check-out time must be after check-in time.']);
                    exit;
                }
                $check_out_time = $check_out_dt->format('Y-m-d H:i:s');
            }
            $check_in_time = $check_in_dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
            exit;
        }

        // Determine period based on check-in time
        $hour = (int)$check_in_dt->format('H');
        $period = ($hour >= 12) ? 'Afternoon' : 'Day';

        // Set initial status as Pending
        $status = 'Pending';

        // Insert the attendance record
        $stmt = $conn->prepare("INSERT INTO afpro_attendance (user_id, event_id, check_in_time, check_out_time, status, period) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("iissss", $user_id, $event_id, $check_in_time, $check_out_time, $status, $period);

        if ($stmt->execute()) {
            ob_end_clean();
            echo json_encode(['success' => true, 'message' => 'Attendance submitted successfully! Waiting for approval.']);
        } else {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        ob_end_clean();
        error_log("Error in AttendanceCreate.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    } finally {
        if (isset($conn) && $conn) {
            $conn->close();
        }
        exit;
    }
}

ob_end_clean();
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;