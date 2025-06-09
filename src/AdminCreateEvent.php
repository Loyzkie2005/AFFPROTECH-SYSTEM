<?php
session_start();

// Check if the user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Include database connection
require_once 'DBconnection.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $location = trim($_POST['location'] ?? '');

    // Save entered data in session to refill form if needed
    $_SESSION['form_data'] = [
        'title' => $title,
        'description' => $description,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'location' => $location
    ];

    // Basic validation
    if (empty($title) || empty($start_date) || empty($end_date) || empty($location)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // Validate date format and order
    try {
        $start_dt = new DateTime($start_date);
        $end_dt = new DateTime($end_date);
        
        if ($end_dt <= $start_dt) {
            echo json_encode(['success' => false, 'message' => 'End date must be after start date.']);
            exit;
        }
        
        // Format dates for database
        $start_date = $start_dt->format('Y-m-d H:i:s');
        $end_date = $end_dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
        exit;
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO afpro_events (title, description, start_date, end_date, location) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $location);

    // Execute the statement
    if ($stmt->execute()) {
        // Clear any stored form data on success
        unset($_SESSION['form_data']);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Event created successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error creating event: ' . $conn->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    // If not POST request, return error
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method. Please use POST.'
    ]);
}
?>